<?php

use App\Livewire\Contacts\Index;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

test('the contacts module requires authentication', function () {
    $this->get(route('contacts.index'))->assertRedirect(route('login'));
});

test('it lists contacts, filters them by search term and hides those of deleted organizations', function () {
    $this->actingAs(User::factory()->create());

    Contact::factory()->create(['name' => 'Ana Torres', 'organization_id' => Organization::factory()->create(['name' => 'Salud'])]);
    Contact::factory()->create(['name' => 'Luis Pérez', 'organization_id' => Organization::factory()->create(['name' => 'Editorial'])]);
    Contact::factory()->create(['name' => 'Rosa Gómez', 'organization_id' => Organization::factory()->trashed()]);

    Livewire::test(Index::class)
        ->assertSee('Ana Torres')
        ->assertSee('Luis Pérez')
        ->assertDontSee('Rosa Gómez')
        ->set('search', 'editorial')
        ->assertSee('Luis Pérez')
        ->assertDontSee('Ana Torres');
});

test('a user can create a contact', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->set('email', 'ana@example.com')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('contacts', [
        'organization_id' => $organization->id,
        'name' => 'Ana Torres',
        'position' => null,
    ]);
});

test('a contact stores an address and a maps url', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->set('address', 'Av. Reforma 100, CDMX')
        ->set('maps_url', 'https://maps.app.goo.gl/abc123')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('contacts', [
        'name' => 'Ana Torres',
        'address' => 'Av. Reforma 100, CDMX',
        'maps_url' => 'https://maps.app.goo.gl/abc123',
    ]);
});

test('the maps url must be an http or https url', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    $component = Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres');

    foreach (['not a url', 'javascript://%0Aalert(1)', 'ftp://example.com/map'] as $invalid) {
        $component->set('maps_url', $invalid)->call('save')->assertHasErrors(['maps_url']);
    }

    $component->set('maps_url', '')->call('save')->assertHasNoErrors();
});

test('the contact requires a name and an existing organization', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', '999')
        ->set('email', 'invalid')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'organization_id' => 'exists', 'email' => 'email']);
});

test('marking a contact as primary demotes the previous primary contact of that organization', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    $previous = Contact::factory()->primary()->for($organization)->create();
    $other = Contact::factory()->primary()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->set('is_primary', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($previous->refresh()->is_primary)->toBeFalse()
        ->and($other->refresh()->is_primary)->toBeTrue()
        ->and(Contact::firstWhere('name', 'Ana Torres')->is_primary)->toBeTrue();
});

test('an admin can delete a contact and a non-admin cannot', function () {
    $contact = Contact::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('confirmDelete', $contact->id)
        ->assertForbidden();

    $this->assertNotSoftDeleted($contact);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Index::class)
        ->call('confirmDelete', $contact->id)
        ->call('delete');

    $this->assertSoftDeleted($contact);
});
