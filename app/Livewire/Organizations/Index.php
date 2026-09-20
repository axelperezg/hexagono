<?php

namespace App\Livewire\Organizations;

use App\Models\Organization;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM module to create, edit and delete organizations (routed at
 * /organizaciones). Every authenticated user can manage them; only
 * admins can delete.
 */
#[Title('Organizaciones')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    public ?int $editingOrganizationId = null;

    public ?int $deletingOrganizationId = null;

    public string $name = '';

    public string $sector = '';

    public string $website = '';

    public string $phone = '';

    public string $notes = '';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the form modal to create a new organization.
     */
    public function createOrganization(): void
    {
        $this->resetForm();

        Flux::modal('organization-form')->show();
    }

    /**
     * Open the form modal pre-filled to edit an existing organization.
     */
    public function editOrganization(int $organizationId): void
    {
        $organization = Organization::findOrFail($organizationId);

        $this->resetForm();
        $this->editingOrganizationId = $organization->id;
        $this->name = $organization->name;
        $this->sector = (string) $organization->sector;
        $this->website = (string) $organization->website;
        $this->phone = (string) $organization->phone;
        $this->notes = (string) $organization->notes;

        Flux::modal('organization-form')->show();
    }

    /**
     * Create or update the organization being edited, depending on
     * whether editingOrganizationId is set.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        $attributes = array_map(fn (?string $value) => $value === '' ? null : $value, $validated);

        if ($this->editingOrganizationId) {
            Organization::findOrFail($this->editingOrganizationId)->update($attributes);

            Flux::toast(variant: 'success', text: __('Organización actualizada.'));
        } else {
            Organization::create($attributes);

            Flux::toast(variant: 'success', text: __('Organización creada.'));
        }

        Flux::modal('organization-form')->close();

        $this->resetForm();
    }

    /**
     * Open the confirmation modal before deleting an organization.
     */
    public function confirmDelete(int $organizationId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->deletingOrganizationId = $organizationId;

        Flux::modal('confirm-organization-delete')->show();
    }

    #[Computed]
    public function deletingOrganization(): ?Organization
    {
        return $this->deletingOrganizationId ? Organization::find($this->deletingOrganizationId) : null;
    }

    /**
     * Soft-delete the organization selected via confirmDelete().
     */
    public function delete(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        Organization::findOrFail($this->deletingOrganizationId)->delete();

        Flux::toast(variant: 'success', text: __('Organización eliminada.'));

        Flux::modal('confirm-organization-delete')->close();

        $this->deletingOrganizationId = null;
    }

    public function render(): View
    {
        return view('livewire.organizations.index', [
            'organizations' => $this->organizations(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Organization>
     */
    private function organizations(): LengthAwarePaginator
    {
        return Organization::query()
            ->withCount(['contacts', 'opportunities'])
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('name', "%{$this->search}%")
                        ->orWhereLike('sector', "%{$this->search}%")
                )
            )
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Reset the form back to its blank, "create" state.
     */
    private function resetForm(): void
    {
        $this->reset(['editingOrganizationId', 'name', 'sector', 'website', 'phone', 'notes']);
        $this->resetErrorBag();
    }
}
