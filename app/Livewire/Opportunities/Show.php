<?php

namespace App\Livewire\Opportunities;

use App\Enums\InteractionType;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Opportunity;
use App\Models\OpportunityStageChange;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Detail page of an opportunity (routed at /oportunidades/{opportunity}):
 * shows its summary and tags, lets any authenticated user move it through
 * the pipeline, log interactions and manage follow-up tasks, lists its stage
 * history, and lets admins delete interactions and tasks.
 */
class Show extends Component
{
    public Opportunity $opportunity;

    public string $pipeline_stage_id = '';

    public string $type = '';

    public string $subject = '';

    public string $occurred_at = '';

    public string $notes = '';

    /**
     * @var array<int, string>
     */
    public array $contactIds = [];

    public ?int $editingTaskId = null;

    public string $task_title = '';

    public string $task_notes = '';

    public string $task_due_date = '';

    public string $task_user_id = '';

    public function mount(Opportunity $opportunity): void
    {
        abort_if($opportunity->organization === null, 404);

        $this->opportunity = $opportunity->load('tags');
        $this->pipeline_stage_id = (string) $opportunity->pipeline_stage_id;
        $this->resetInteractionForm();
    }

    public function title(): string
    {
        return $this->opportunity->title;
    }

    /**
     * Move the opportunity to the stage picked in the header select.
     */
    public function updatedPipelineStageId(): void
    {
        $this->validateOnly('pipeline_stage_id', [
            'pipeline_stage_id' => ['required', Rule::exists('pipeline_stages', 'id')],
        ]);

        $this->opportunity->update(['pipeline_stage_id' => $this->pipeline_stage_id]);

        Flux::toast(variant: 'success', text: __('Etapa actualizada.'));
    }

    /**
     * Open the modal to log a new interaction.
     */
    public function createInteraction(): void
    {
        $this->resetInteractionForm();

        Flux::modal('interaction-form')->show();
    }

    /**
     * Log an interaction on this opportunity, linking it to the chosen
     * contacts (which must belong to the opportunity's organization).
     */
    public function saveInteraction(): void
    {
        $validated = $this->validate([
            'type' => ['required', Rule::enum(InteractionType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'contactIds' => ['array'],
            'contactIds.*' => [
                Rule::exists('contacts', 'id')
                    ->where('organization_id', $this->opportunity->organization_id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $interaction = $this->opportunity->interactions()->create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'occurred_at' => $validated['occurred_at'],
            'notes' => $validated['notes'] === '' ? null : $validated['notes'],
        ]);

        $interaction->contacts()->sync($validated['contactIds']);

        Flux::toast(variant: 'success', text: __('Interacción registrada.'));

        Flux::modal('interaction-form')->close();

        $this->resetInteractionForm();
    }

    /**
     * Delete an interaction of this opportunity. Admins only.
     */
    public function deleteInteraction(int $interactionId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->opportunity->interactions()->findOrFail($interactionId)->delete();

        Flux::toast(variant: 'success', text: __('Interacción eliminada.'));
    }

    /**
     * Open the modal to add a follow-up task, assigned to the current
     * user by default.
     */
    public function createTask(): void
    {
        $this->resetTaskForm();

        Flux::modal('task-form')->show();
    }

    /**
     * Open the task modal pre-filled to edit one of this opportunity's tasks.
     */
    public function editTask(int $taskId): void
    {
        $task = $this->opportunity->tasks()->findOrFail($taskId);

        $this->resetTaskForm();
        $this->editingTaskId = $task->id;
        $this->task_title = $task->title;
        $this->task_notes = (string) $task->notes;
        $this->task_due_date = $task->due_date->format('Y-m-d');
        $this->task_user_id = (string) $task->user_id;

        Flux::modal('task-form')->show();
    }

    /**
     * Create or update the task being edited, depending on whether
     * editingTaskId is set.
     */
    public function saveTask(): void
    {
        $validated = $this->validate([
            'task_title' => ['required', 'string', 'max:255'],
            'task_notes' => ['nullable', 'string', 'max:5000'],
            'task_due_date' => ['required', 'date'],
            'task_user_id' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $attributes = [
            'title' => $validated['task_title'],
            'notes' => $validated['task_notes'] === '' ? null : $validated['task_notes'],
            'due_date' => $validated['task_due_date'],
            'user_id' => $validated['task_user_id'] === '' ? null : $validated['task_user_id'],
        ];

        if ($this->editingTaskId) {
            $this->opportunity->tasks()->findOrFail($this->editingTaskId)->update($attributes);

            Flux::toast(variant: 'success', text: __('Tarea actualizada.'));
        } else {
            $this->opportunity->tasks()->create($attributes);

            Flux::toast(variant: 'success', text: __('Tarea creada.'));
        }

        Flux::modal('task-form')->close();

        $this->resetTaskForm();
    }

    /**
     * Mark a task as completed, or reopen it if it already was.
     */
    public function toggleTask(int $taskId): void
    {
        $task = $this->opportunity->tasks()->findOrFail($taskId);

        $task->update(['completed_at' => $task->isCompleted() ? null : now()]);
    }

    /**
     * Delete a task of this opportunity. Admins only.
     */
    public function deleteTask(int $taskId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->opportunity->tasks()->findOrFail($taskId)->delete();

        Flux::toast(variant: 'success', text: __('Tarea eliminada.'));
    }

    /**
     * Pending tasks first (soonest due date first), then completed ones.
     *
     * @return Collection<int, Task>
     */
    #[Computed]
    public function tasks(): Collection
    {
        return $this->opportunity->tasks()
            ->with('assignee')
            ->orderByRaw('completed_at is not null')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * @return Collection<int, OpportunityStageChange>
     */
    #[Computed]
    public function stageChanges(): Collection
    {
        return $this->opportunity->stageChanges()
            ->with(['fromStage', 'toStage', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    /**
     * @return Collection<int, PipelineStage>
     */
    #[Computed]
    public function stages(): Collection
    {
        return PipelineStage::orderBy('position')->get();
    }

    /**
     * Contacts of the opportunity's organization, offered when logging
     * an interaction.
     *
     * @return Collection<int, Contact>
     */
    #[Computed]
    public function contacts(): Collection
    {
        return $this->opportunity->organization->contacts()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Interaction>
     */
    #[Computed]
    public function interactions(): Collection
    {
        return $this->opportunity->interactions()
            ->with(['user', 'contacts'])
            ->orderByDesc('occurred_at')
            ->get();
    }

    /**
     * @return array<int, InteractionType>
     */
    #[Computed]
    public function interactionTypes(): array
    {
        return InteractionType::cases();
    }

    public function render(): View
    {
        return view('livewire.opportunities.show')->title($this->title());
    }

    /**
     * Reset the task form to a blank entry assigned to the current user.
     */
    private function resetTaskForm(): void
    {
        $this->reset(['editingTaskId', 'task_title', 'task_notes', 'task_due_date']);
        $this->task_user_id = (string) Auth::id();
        $this->resetErrorBag();
    }

    /**
     * Reset the interaction form to a blank entry dated now.
     */
    private function resetInteractionForm(): void
    {
        $this->reset(['subject', 'notes', 'contactIds']);
        $this->type = InteractionType::Call->value;
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
    }
}
