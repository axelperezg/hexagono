<?php

namespace App\Livewire\Users;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin module to create, edit, view and delete application users
 * (routed at /usuarios, restricted to the Admin role — see the
 * "admin" middleware alias in bootstrap/app.php).
 */
#[Title('Usuarios')]
class Index extends Component
{
    use ProfileValidationRules, WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    public ?int $editingUserId = null;

    public ?int $viewingUserId = null;

    public ?int $deletingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public function mount(): void
    {
        $this->role = UserRole::User->value;
    }

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the form modal to create a new user.
     */
    public function createUser(): void
    {
        $this->resetForm();

        Flux::modal('user-form')->show();
    }

    /**
     * Open the form modal pre-filled to edit an existing user. The
     * password fields are left blank — the current password is kept
     * unless the admin types a new one.
     */
    public function editUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->resetForm();
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;

        Flux::modal('user-form')->show();
    }

    /**
     * Create or update the user being edited, depending on whether
     * editingUserId is set.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);

            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]);

            if (array_key_exists('password', $validated)) {
                $user->password = $validated['password'];
            }

            $user->save();

            Flux::toast(variant: 'success', text: __('Usuario actualizado.'));
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            Flux::toast(variant: 'success', text: __('Usuario creado.'));
        }

        Flux::modal('user-form')->close();

        $this->resetForm();
    }

    /**
     * Select a user for the read-only detail modal.
     */
    public function show(int $userId): void
    {
        $this->viewingUserId = $userId;

        Flux::modal('user-detalle')->show();
    }

    #[Computed]
    public function viewingUser(): ?User
    {
        return $this->viewingUserId ? User::find($this->viewingUserId) : null;
    }

    /**
     * Open the confirmation modal before deleting a user.
     */
    public function confirmDelete(int $userId): void
    {
        $this->deletingUserId = $userId;

        Flux::modal('confirm-user-delete')->show();
    }

    #[Computed]
    public function deletingUser(): ?User
    {
        return $this->deletingUserId ? User::find($this->deletingUserId) : null;
    }

    /**
     * Delete the user selected via confirmDelete(). Admins may not
     * delete their own account through this module.
     */
    public function delete(): void
    {
        $user = User::findOrFail($this->deletingUserId);

        if ($user->id === Auth::id()) {
            Flux::toast(variant: 'danger', text: __('No puedes eliminar tu propio usuario.'));
        } else {
            $user->delete();

            Flux::toast(variant: 'success', text: __('Usuario eliminado.'));
        }

        Flux::modal('confirm-user-delete')->close();

        $this->deletingUserId = null;
    }

    /**
     * @return array<int, UserRole>
     */
    #[Computed]
    public function roles(): array
    {
        return UserRole::cases();
    }

    public function render(): View
    {
        return view('livewire.users.index', [
            'users' => $this->users(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    private function users(): LengthAwarePaginator
    {
        return User::query()
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('name', "%{$this->search}%")
                        ->orWhereLike('email', "%{$this->search}%")
                )
            )
            ->latest()
            ->paginate(15);
    }

    /**
     * Validation rules for the create/edit form. The password is only
     * required when creating a user, or when editing one and the admin
     * has typed a new password into the (otherwise blank) field.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        $rules = [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($this->editingUserId),
            'role' => ['required', Rule::enum(UserRole::class)],
        ];

        if (! $this->editingUserId || $this->password !== '') {
            $rules['password'] = ['required', 'string', Password::default(), 'confirmed'];
        }

        return $rules;
    }

    /**
     * Reset the create/edit form back to its blank, "create" state.
     */
    private function resetForm(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation']);
        $this->role = UserRole::User->value;
        $this->resetErrorBag();
    }
}
