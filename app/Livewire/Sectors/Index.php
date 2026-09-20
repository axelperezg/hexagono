<?php

namespace App\Livewire\Sectors;

use App\Models\Sector;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM catalog to create, edit and delete the sectors organizations belong
 * to (routed at /sectores). Every authenticated user can manage them; only
 * admins can delete, and only while no organization uses the sector.
 */
#[Title('Sectores')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    public ?int $editingSectorId = null;

    public ?int $deletingSectorId = null;

    public string $name = '';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the form modal to create a new sector.
     */
    public function createSector(): void
    {
        $this->resetForm();

        Flux::modal('sector-form')->show();
    }

    /**
     * Open the form modal pre-filled to edit an existing sector.
     */
    public function editSector(int $sectorId): void
    {
        $sector = Sector::findOrFail($sectorId);

        $this->resetForm();
        $this->editingSectorId = $sector->id;
        $this->name = $sector->name;

        Flux::modal('sector-form')->show();
    }

    /**
     * Create or update the sector being edited, depending on whether
     * editingSectorId is set.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        if ($this->editingSectorId) {
            Sector::findOrFail($this->editingSectorId)->update($validated);

            Flux::toast(variant: 'success', text: __('Sector actualizado.'));
        } else {
            Sector::create($validated);

            Flux::toast(variant: 'success', text: __('Sector creado.'));
        }

        Flux::modal('sector-form')->close();

        $this->resetForm();
    }

    /**
     * Open the confirmation modal before deleting a sector.
     */
    public function confirmDelete(int $sectorId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->deletingSectorId = $sectorId;

        Flux::modal('confirm-sector-delete')->show();
    }

    #[Computed]
    public function deletingSector(): ?Sector
    {
        return $this->deletingSectorId ? Sector::find($this->deletingSectorId) : null;
    }

    /**
     * Delete the sector selected via confirmDelete(), unless an
     * organization still uses it.
     */
    public function delete(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $sector = Sector::findOrFail($this->deletingSectorId);

        if ($sector->isInUse()) {
            Flux::toast(variant: 'danger', text: __('No se puede eliminar: hay organizaciones con este sector.'));
        } else {
            $sector->delete();

            Flux::toast(variant: 'success', text: __('Sector eliminado.'));
        }

        Flux::modal('confirm-sector-delete')->close();

        $this->deletingSectorId = null;
    }

    public function render(): View
    {
        return view('livewire.sectors.index', [
            'sectors' => $this->sectors(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Sector>
     */
    private function sectors(): LengthAwarePaginator
    {
        return Sector::query()
            ->withCount('organizations')
            ->when($this->search !== '', fn ($query) => $query->whereLike('name', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('sectors', 'name')->ignore($this->editingSectorId)],
        ];
    }

    /**
     * Reset the form back to its blank, "create" state.
     */
    private function resetForm(): void
    {
        $this->reset(['editingSectorId', 'name']);
        $this->resetErrorBag();
    }
}
