<?php

namespace App\Livewire\Opportunities;

use App\Enums\BudgetItem;
use App\Enums\Priority;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM module to create, edit and delete opportunities (routed at
 * /oportunidades). Every authenticated user sees all opportunities and
 * can manage them; only admins can delete. Each row links to the detail
 * page (App\Livewire\Opportunities\Show) where interactions are logged.
 */
#[Title('Oportunidades')]
class Index extends Component
{
    use WithPagination;

    /**
     * Currencies an opportunity's estimated amount can be expressed in.
     *
     * @var array<int, string>
     */
    public const CURRENCIES = ['MXN', 'USD'];

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    #[Url(as: 'etapa', history: true)]
    public string $stageFilter = '';

    #[Url(as: 'ejercicio', history: true)]
    public string $fiscalYearFilter = '';

    public ?int $editingOpportunityId = null;

    public ?int $deletingOpportunityId = null;

    public string $organization_id = '';

    public string $pipeline_stage_id = '';

    public string $user_id = '';

    public string $title = '';

    public string $fiscal_year = '';

    public string $budget_item = '';

    public string $campaign = '';

    public string $version = '';

    public string $priority = '';

    public string $estimated_amount = '';

    public string $currency = 'MXN';

    public string $expected_close_date = '';

    public string $notes = '';

    #[Url(as: 'prioridad', history: true)]
    public string $priorityFilter = '';

    /**
     * Default the fiscal year filter to the current year.
     */
    public function mount(): void
    {
        if ($this->fiscalYearFilter === '') {
            $this->fiscalYearFilter = (string) now()->year;
        }
    }

    /**
     * Reset to the first page whenever a filter changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFiscalYearFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Open the form modal to create a new opportunity, owned by the
     * current user and placed in the first pipeline stage by default.
     */
    public function createOpportunity(): void
    {
        $this->resetForm();

        Flux::modal('opportunity-form')->show();
    }

    /**
     * Open the form modal pre-filled to edit an existing opportunity.
     */
    public function editOpportunity(int $opportunityId): void
    {
        $opportunity = Opportunity::findOrFail($opportunityId);

        $this->resetForm();
        $this->editingOpportunityId = $opportunity->id;
        $this->organization_id = (string) $opportunity->organization_id;
        $this->pipeline_stage_id = (string) $opportunity->pipeline_stage_id;
        $this->user_id = (string) $opportunity->user_id;
        $this->title = $opportunity->title;
        $this->fiscal_year = (string) $opportunity->fiscal_year;
        $this->budget_item = (string) $opportunity->budget_item?->value;
        $this->campaign = (string) $opportunity->campaign;
        $this->version = (string) $opportunity->version;
        $this->priority = (string) $opportunity->priority?->value;
        $this->estimated_amount = (string) $opportunity->estimated_amount;
        $this->currency = $opportunity->currency;
        $this->expected_close_date = (string) $opportunity->expected_close_date?->format('Y-m-d');
        $this->notes = (string) $opportunity->notes;

        Flux::modal('opportunity-form')->show();
    }

    /**
     * Create or update the opportunity being edited, depending on
     * whether editingOpportunityId is set.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        $attributes = array_map(
            fn (?string $value) => $value === '' ? null : $value,
            $validated,
        );

        if ($this->editingOpportunityId) {
            $opportunity = Opportunity::findOrFail($this->editingOpportunityId);
            $opportunity->update($attributes);

            Flux::toast(variant: 'success', text: __('Oportunidad actualizada.'));
        } else {
            $opportunity = Opportunity::create($attributes);

            Flux::toast(variant: 'success', text: __('Oportunidad creada.'));
        }

        Flux::modal('opportunity-form')->close();

        $this->resetForm();
    }

    /**
     * Open the confirmation modal before deleting an opportunity.
     */
    public function confirmDelete(int $opportunityId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->deletingOpportunityId = $opportunityId;

        Flux::modal('confirm-opportunity-delete')->show();
    }

    #[Computed]
    public function deletingOpportunity(): ?Opportunity
    {
        return $this->deletingOpportunityId ? Opportunity::find($this->deletingOpportunityId) : null;
    }

    /**
     * Soft-delete the opportunity selected via confirmDelete().
     */
    public function delete(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        Opportunity::findOrFail($this->deletingOpportunityId)->delete();

        Flux::toast(variant: 'success', text: __('Oportunidad eliminada.'));

        Flux::modal('confirm-opportunity-delete')->close();

        $this->deletingOpportunityId = null;
    }

    /**
     * @return Collection<int, Organization>
     */
    #[Computed]
    public function organizations(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
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
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function render(): View
    {
        return view('livewire.opportunities.index', [
            'opportunities' => $this->opportunities(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Opportunity>
     */
    private function opportunities(): LengthAwarePaginator
    {
        return Opportunity::query()
            ->with(['organization', 'stage', 'owner'])
            ->whereHas('organization')
            ->when(
                $this->stageFilter !== '',
                fn ($query) => $query->where('pipeline_stage_id', $this->stageFilter)
            )
            ->when(
                $this->fiscalYearFilter !== '',
                fn ($query) => $query->where('fiscal_year', $this->fiscalYearFilter)
            )
            ->when(
                $this->priorityFilter !== '',
                fn ($query) => $query->where('priority', $this->priorityFilter)
            )
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('title', "%{$this->search}%")
                        ->orWhereHas('organization', fn ($query) => $query->whereLike('name', "%{$this->search}%"))
                )
            )
            ->latest()
            ->paginate(15);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'organization_id' => ['required', Rule::exists('organizations', 'id')->whereNull('deleted_at')],
            'pipeline_stage_id' => ['required', Rule::exists('pipeline_stages', 'id')],
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'budget_item' => ['nullable', Rule::enum(BudgetItem::class)],
            'campaign' => ['nullable', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'fiscal_year' => ['required', 'integer', 'between:'.Opportunity::FISCAL_YEAR_MIN.','.Opportunity::FISCAL_YEAR_MAX],
            'estimated_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['required', Rule::in(self::CURRENCIES)],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Reset the form back to its blank, "create" state.
     */
    private function resetForm(): void
    {
        $this->reset(['editingOpportunityId', 'organization_id', 'title', 'estimated_amount', 'expected_close_date', 'notes', 'budget_item', 'campaign', 'version', 'priority']);
        $this->currency = self::CURRENCIES[0];
        $this->fiscal_year = (string) now()->year;
        $this->user_id = (string) Auth::id();
        $this->pipeline_stage_id = (string) $this->stages()->first()?->id;
        $this->resetErrorBag();
    }
}
