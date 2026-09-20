<?php

use App\Livewire\Organizations\Index;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

test('the organizations module requires authentication', function () {
    $this->get(route('organizations.index'))->assertRedirect(route('login'));
});

test('it lists organizations and filters them by search term', function () {
    $this->actingAs(User::factory()->create());

    Organization::factory()->create(['name' => 'Secretaría de Salud', 'sector' => 'Gobierno']);
    Organization::factory()->create(['name' => 'Editorial Norte', 'sector' => 'Medios']);

    Livewire::test(Index::class)
        ->assertSee('Secretaría de Salud')
        ->assertSee('Editorial Norte')
        ->set('search', 'salud')
        ->assertSee('Secretaría de Salud')
        ->assertDontSee('Editorial Norte');
});

test('a user can create an organization', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('sector', 'Gobierno')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('organizations', ['name' => 'Secretaría de Salud', 'sector' => 'Gobierno', 'website' => null]);
});

test('the organization name is required and the website must be a url', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('website', 'not a url')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'website' => 'url']);
});

test('a user can edit an organization', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create(['name' => 'Old name']);

    Livewire::test(Index::class)
        ->call('editOrganization', $organization->id)
        ->assertSet('name', 'Old name')
        ->set('name', 'New name')
        ->call('save')
        ->assertHasNoErrors();

    expect($organization->refresh()->name)->toBe('New name');
});

test('an admin can delete an organization, which is soft deleted', function () {
    $this->actingAs(User::factory()->admin()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $organization->id)
        ->call('delete');

    $this->assertSoftDeleted($organization);
});

test('a non-admin user cannot delete an organization', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $organization->id)
        ->assertForbidden();

    $this->assertNotSoftDeleted($organization);
});
