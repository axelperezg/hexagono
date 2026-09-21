<?php

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Models\Sector;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * CRM module to create, edit and delete organizations (routed at
 * /organizaciones). Every authenticated user can manage them; only
 * admins can delete.
 */
#[Title('Organizaciones')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    public ?int $editingOrganizationId = null;

    public ?int $deletingOrganizationId = null;

    public string $name = '';

    public string $acronym = '';

    public string $sectorId = '';

    public ?TemporaryUploadedFile $logo = null;

    public bool $removeLogo = false;

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
        $this->acronym = (string) $organization->acronym;
        $this->sectorId = (string) $organization->sector_id;
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

        $attributes = array_map(
            fn (?string $value) => $value === '' ? null : $value,
            Arr::except($validated, ['sectorId', 'logo']),
        );
        $attributes['sector_id'] = $validated['sectorId'] === '' ? null : (int) $validated['sectorId'];

        $organization = $this->editingOrganizationId ? Organization::findOrFail($this->editingOrganizationId) : null;
        $previousLogoPath = $organization?->logo_path;

        if ($this->logo) {
            $attributes['logo_path'] = $this->logo->store('organization-logos', 'public');
        } elseif ($this->removeLogo) {
            $attributes['logo_path'] = null;
        }

        if ($organization) {
            $organization->update($attributes);

            Flux::toast(variant: 'success', text: __('Organización actualizada.'));
        } else {
            $organization = Organization::create($attributes);

            Flux::toast(variant: 'success', text: __('Organización creada.'));
        }

        if ($previousLogoPath && $previousLogoPath !== $organization->logo_path) {
            Storage::disk('public')->delete($previousLogoPath);
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
    public function editingOrganization(): ?Organization
    {
        return $this->editingOrganizationId ? Organization::find($this->editingOrganizationId) : null;
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

    /**
     * Sectors offered in the form.
     *
     * @return Collection<int, Sector>
     */
    #[Computed]
    public function availableSectors(): Collection
    {
        return Sector::orderBy('name')->get();
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
            ->with('sector')
            ->withCount(['contacts', 'opportunities'])
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('name', "%{$this->search}%")
                        ->orWhereLike('acronym', "%{$this->search}%")
                        ->orWhereHas('sector', fn ($query) => $query->whereLike('name', "%{$this->search}%"))
                )
            )
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'acronym' => ['nullable', 'string', 'max:50'],
            // SVG is left out on purpose: it can carry scripts.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'sectorId' => ['nullable', Rule::exists('sectors', 'id')],
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
        $this->reset(['editingOrganizationId', 'name', 'acronym', 'sectorId', 'logo', 'removeLogo', 'website', 'phone', 'notes']);
        $this->resetErrorBag();
    }
}
