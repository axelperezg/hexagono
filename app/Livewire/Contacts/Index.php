<?php

namespace App\Livewire\Contacts;

use App\Enums\PhoneType;
use App\Models\Contact;
use App\Models\Organization;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM module to create, edit and delete contacts, the people inside
 * an organization (routed at /contactos). Every authenticated user can
 * manage them; only admins can delete.
 */
#[Title('Contactos')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    public ?int $editingContactId = null;

    public ?int $deletingContactId = null;

    public string $organization_id = '';

    public string $name = '';

    public string $position = '';

    public string $email = '';

    /**
     * Rows come from the browser, so their shape is only trusted after validation.
     *
     * @var array<int, mixed>
     */
    public array $phones = [];

    public string $address = '';

    public string $maps_url = '';

    public bool $is_primary = false;

    public string $notes = '';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the form modal to create a new contact.
     */
    public function createContact(): void
    {
        $this->resetForm();
        $this->addPhone();

        Flux::modal('contact-form')->show();
    }

    /**
     * Append a blank phone row to the repeater.
     */
    public function addPhone(): void
    {
        $this->phones[] = ['type' => PhoneType::Mobile->value, 'number' => ''];
    }

    /**
     * Remove a phone row from the repeater.
     */
    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);

        $this->phones = array_values($this->phones);
    }

    /**
     * Open the form modal pre-filled to edit an existing contact.
     */
    public function editContact(int $contactId): void
    {
        $contact = Contact::findOrFail($contactId);

        $this->resetForm();
        $this->editingContactId = $contact->id;
        $this->organization_id = (string) $contact->organization_id;
        $this->name = $contact->name;
        $this->position = (string) $contact->position;
        $this->email = (string) $contact->email;
        $this->phones = $contact->phones
            ->map(fn ($phone) => ['type' => $phone->type->value, 'number' => $phone->number])
            ->all();
        $this->address = (string) $contact->address;
        $this->maps_url = (string) $contact->maps_url;
        $this->is_primary = $contact->is_primary;
        $this->notes = (string) $contact->notes;

        Flux::modal('contact-form')->show();
    }

    /**
     * Create or update the contact being edited. Marking a contact as
     * primary demotes the organization's previous primary contact.
     */
    public function save(): void
    {
        // Rows left blank are dropped instead of failing validation.
        $this->phones = array_values(array_filter(
            $this->phones,
            fn (mixed $phone) => is_array($phone) && trim((string) ($phone['number'] ?? '')) !== '',
        ));

        $validated = $this->validate($this->rules());

        $phones = $validated['phones'];
        $attributes = array_map(
            fn (mixed $value) => $value === '' ? null : $value,
            Arr::except($validated, ['phones']),
        );

        DB::transaction(function () use ($attributes, $phones) {
            if ($attributes['is_primary']) {
                Contact::where('organization_id', $attributes['organization_id'])
                    ->when($this->editingContactId, fn ($query) => $query->whereKeyNot($this->editingContactId))
                    ->update(['is_primary' => false]);
            }

            if ($this->editingContactId) {
                $contact = Contact::findOrFail($this->editingContactId);
                $contact->update($attributes);
            } else {
                $contact = Contact::create($attributes);
            }

            $contact->phones()->delete();
            $contact->phones()->createMany(array_map(
                fn (array $phone) => ['type' => $phone['type'], 'number' => trim($phone['number'])],
                $phones,
            ));
        });

        Flux::toast(variant: 'success', text: $this->editingContactId ? __('Contacto actualizado.') : __('Contacto creado.'));

        Flux::modal('contact-form')->close();

        $this->resetForm();
    }

    /**
     * Open the confirmation modal before deleting a contact.
     */
    public function confirmDelete(int $contactId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->deletingContactId = $contactId;

        Flux::modal('confirm-contact-delete')->show();
    }

    #[Computed]
    public function deletingContact(): ?Contact
    {
        return $this->deletingContactId ? Contact::find($this->deletingContactId) : null;
    }

    /**
     * Soft-delete the contact selected via confirmDelete().
     */
    public function delete(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        Contact::findOrFail($this->deletingContactId)->delete();

        Flux::toast(variant: 'success', text: __('Contacto eliminado.'));

        Flux::modal('confirm-contact-delete')->close();

        $this->deletingContactId = null;
    }

    /**
     * Organizations offered in the form's select.
     *
     * @return Collection<int, Organization>
     */
    #[Computed]
    public function organizations(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function render(): View
    {
        return view('livewire.contacts.index', [
            'contacts' => $this->contacts(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    private function contacts(): LengthAwarePaginator
    {
        return Contact::query()
            ->with(['organization', 'phones'])
            ->whereHas('organization')
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('name', "%{$this->search}%")
                        ->orWhereLike('email', "%{$this->search}%")
                        ->orWhereLike('position', "%{$this->search}%")
                        ->orWhereHas('organization', fn ($query) => $query->whereLike('name', "%{$this->search}%"))
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
            'organization_id' => ['required', Rule::exists('organizations', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phones' => ['array'],
            'phones.*.type' => ['required', Rule::enum(PhoneType::class)],
            'phones.*.number' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            // Restricted to http(s) because the value is rendered as a link.
            'maps_url' => ['nullable', 'url:http,https', 'max:2048'],
            'is_primary' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Reset the form back to its blank, "create" state.
     */
    private function resetForm(): void
    {
        $this->reset(['editingContactId', 'organization_id', 'name', 'position', 'email', 'phones', 'address', 'maps_url', 'is_primary', 'notes']);
        $this->resetErrorBag();
    }
}
