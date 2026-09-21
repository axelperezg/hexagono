<?php

namespace App\Livewire\Tasks;

use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Create/edit modal for tasks, shared by the tasks agenda (/tareas) and the
 * opportunity page. Open it with the "create-task" or "edit-task" events;
 * it dispatches "task-saved" so the host page can refresh its list. When
 * mounted with an opportunity id, new tasks are pre-linked to it.
 */
class Form extends Component
{
    public ?int $defaultOpportunityId = null;

    public ?int $editingTaskId = null;

    public bool $hasOpportunity = false;

    public string $opportunity_id = '';

    public string $user_id = '';

    public string $concept = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $notes = '';

    /**
     * Repeater rows: the actions carried out on the task and their dates.
     *
     * @var array<int, array{description: string, performed_at: string}>
     */
    public array $taskActions = [];

    public function mount(?int $opportunityId = null): void
    {
        $this->defaultOpportunityId = $opportunityId;
    }

    /**
     * Open the modal to create a task, assigned to the current user and
     * starting today.
     */
    #[On('create-task')]
    public function createTask(): void
    {
        $this->resetForm();

        Flux::modal('task-form')->show();
    }

    /**
     * Open the modal pre-filled to edit an existing task.
     */
    #[On('edit-task')]
    public function editTask(int $taskId): void
    {
        $task = Task::with('actions')->findOrFail($taskId);

        $this->resetForm();
        $this->editingTaskId = $task->id;
        $this->hasOpportunity = $task->opportunity_id !== null;
        $this->opportunity_id = (string) $task->opportunity_id;
        $this->user_id = (string) $task->user_id;
        $this->concept = $task->concept;
        $this->start_date = $task->start_date->format('Y-m-d');
        $this->end_date = $task->end_date->format('Y-m-d');
        $this->notes = (string) $task->notes;
        $this->taskActions = $task->actions
            ->map(fn ($action) => [
                'description' => $action->description,
                'performed_at' => $action->performed_at->format('Y-m-d'),
            ])
            ->all();

        Flux::modal('task-form')->show();
    }

    /**
     * Append a blank action row, dated today, to the repeater.
     */
    public function addAction(): void
    {
        $this->taskActions[] = ['description' => '', 'performed_at' => today()->format('Y-m-d')];
    }

    /**
     * Remove an action row from the repeater.
     */
    public function removeAction(int $index): void
    {
        unset($this->taskActions[$index]);

        $this->taskActions = array_values($this->taskActions);
    }

    /**
     * Create or update the task being edited, replacing its actions with
     * the repeater rows.
     */
    public function save(): void
    {
        // Rows left without a description are dropped instead of failing validation.
        $this->taskActions = array_values(array_filter(
            $this->taskActions,
            fn (mixed $action) => is_array($action) && trim((string) ($action['description'] ?? '')) !== '',
        ));

        $validated = $this->validate($this->rules());

        $attributes = [
            'opportunity_id' => $this->hasOpportunity ? $validated['opportunity_id'] : null,
            'user_id' => $validated['user_id'] === '' ? null : $validated['user_id'],
            'concept' => $validated['concept'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] === '' ? null : $validated['notes'],
        ];

        DB::transaction(function () use ($attributes, $validated) {
            if ($this->editingTaskId) {
                $task = Task::findOrFail($this->editingTaskId);
                $task->update($attributes);
                $task->actions()->delete();
            } else {
                $task = Task::create($attributes);
            }

            $task->actions()->createMany($validated['taskActions']);
        });

        Flux::toast(variant: 'success', text: $this->editingTaskId ? __('Tarea actualizada.') : __('Tarea creada.'));

        Flux::modal('task-form')->close();

        $this->dispatch('task-saved');

        $this->resetForm();
    }

    /**
     * Opportunities a task can be linked to.
     *
     * @return Collection<int, Opportunity>
     */
    #[Computed]
    public function opportunities(): Collection
    {
        return Opportunity::query()
            ->with('organization:id,name')
            ->whereHas('organization')
            ->orderBy('title')
            ->get(['id', 'organization_id', 'title']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function render(): View
    {
        return view('livewire.tasks.form');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'hasOpportunity' => ['boolean'],
            'opportunity_id' => [
                Rule::requiredIf($this->hasOpportunity),
                'nullable',
                Rule::exists('opportunities', 'id')->whereNull('deleted_at'),
            ],
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'concept' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'taskActions' => ['array'],
            'taskActions.*.description' => ['required', 'string', 'max:1000'],
            'taskActions.*.performed_at' => ['required', 'date'],
        ];
    }

    /**
     * Reset the form to a blank task assigned to the current user, starting
     * today and linked to the host page's opportunity, if any.
     */
    private function resetForm(): void
    {
        $this->reset(['editingTaskId', 'concept', 'end_date', 'notes', 'taskActions']);
        $this->hasOpportunity = $this->defaultOpportunityId !== null;
        $this->opportunity_id = (string) $this->defaultOpportunityId;
        $this->user_id = (string) Auth::id();
        $this->start_date = today()->format('Y-m-d');
        $this->resetErrorBag();
    }
}
