<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('the users module requires authentication', function () {
    $response = $this->get(route('users.index'));

    $response->assertRedirect(route('login'));
});

test('the users module is refused to an authenticated non-admin user', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::User]));

    $this->get(route('users.index'))->assertForbidden();
});

test('the users module is displayed to an admin', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('users.index'))->assertOk();
});

test('it lists users with the newest first', function () {
    $this->actingAs(User::factory()->admin()->create());

    $older = User::factory()->create(['name' => 'Ana Torres', 'created_at' => now()->subDay()]);
    $newer = User::factory()->create(['name' => 'Luis Pérez', 'created_at' => now()]);

    Livewire::test(Index::class)
        ->assertSeeInOrder([$newer->name, $older->name]);
});

test('it filters users by search term', function () {
    $this->actingAs(User::factory()->admin()->create());

    User::factory()->create(['name' => 'Ana Torres', 'email' => 'ana@example.com']);
    User::factory()->create(['name' => 'Luis Pérez', 'email' => 'luis@example.com']);

    Livewire::test(Index::class)
        ->set('search', 'ana')
        ->assertSee('Ana Torres')
        ->assertDontSee('Luis Pérez');
});

test('an admin can create a user', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Index::class)
        ->call('createUser')
        ->set('name', 'Ana Torres')
        ->set('email', 'ana@example.com')
        ->set('role', UserRole::Admin->value)
        ->set('password', 'a-very-strong-password')
        ->set('password_confirmation', 'a-very-strong-password')
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'ana@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Ana Torres');
    expect($user->role)->toBe(UserRole::Admin);
    expect(Hash::check('a-very-strong-password', $user->password))->toBeTrue();
});

test('creating a user requires the name, email and password', function () {
    $this->actingAs(User::factory()->admin()->create());

    // "role" is not asserted here — the form defaults it to UserRole::User,
    // so it is never left blank the way the other fields can be.
    Livewire::test(Index::class)
        ->call('createUser')
        ->call('save')
        ->assertHasErrors(['name', 'email', 'password']);
});

test('creating a user requires a unique email', function () {
    $this->actingAs(User::factory()->admin()->create());

    $existing = User::factory()->create(['email' => 'ana@example.com']);

    Livewire::test(Index::class)
        ->call('createUser')
        ->set('name', 'Otra Ana')
        ->set('email', $existing->email)
        ->set('role', UserRole::User->value)
        ->set('password', 'a-very-strong-password')
        ->set('password_confirmation', 'a-very-strong-password')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('an admin can edit a user without changing the password', function () {
    $this->actingAs(User::factory()->admin()->create());

    $user = User::factory()->create(['name' => 'Ana Torres', 'role' => UserRole::User]);
    $originalPassword = $user->password;

    Livewire::test(Index::class)
        ->call('editUser', $user->id)
        ->set('name', 'Ana Torres Gómez')
        ->set('role', UserRole::Admin->value)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Ana Torres Gómez');
    expect($user->role)->toBe(UserRole::Admin);
    expect($user->password)->toBe($originalPassword);
});

test('an admin can change a user password while editing', function () {
    $this->actingAs(User::factory()->admin()->create());

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('editUser', $user->id)
        ->set('password', 'a-new-strong-password')
        ->set('password_confirmation', 'a-new-strong-password')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('a-new-strong-password', $user->refresh()->password))->toBeTrue();
});

test('opening a user selects it for the detail modal', function () {
    $this->actingAs(User::factory()->admin()->create());

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('show', $user->id)
        ->assertSet('viewingUserId', $user->id)
        ->assertSee($user->email);
});

test('an admin can delete another user', function () {
    $this->actingAs(User::factory()->admin()->create());

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $user->id)
        ->call('delete');

    expect(User::find($user->id))->toBeNull();
});

test('an admin cannot delete their own account from the module', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('confirmDelete', $admin->id)
        ->call('delete');

    expect(User::find($admin->id))->not->toBeNull();
});
