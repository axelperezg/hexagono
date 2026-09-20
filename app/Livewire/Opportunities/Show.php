<?php

namespace App\Livewire\Opportunities;

use App\Enums\InteractionType;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Detail page of an opportunity (routed at /oportunidades/{opportunity}):
 * shows its summary, lets any authenticated user move it through the
 * pipeline and log interactions, and lets admins delete interactions.
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

    public function mount(Opportunity $opportunity): void
    {
        abort_if($opportunity->organization === null, 404);

        $this->opportunity = $opportunity;
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
