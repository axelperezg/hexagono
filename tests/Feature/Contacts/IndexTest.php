<?php

use App\Enums\PhoneType;
use App\Livewire\Contacts\Index;
use App\Models\Contact;
use App\Models\ContactPhone;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;
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

test('a contact can store several phones with their kind', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->set('phones.0.type', PhoneType::Office->value)
        ->set('phones.0.number', '5512345678')
        ->call('addPhone')
        ->set('phones.1.type', PhoneType::Home->value)
        ->set('phones.1.number', '5587654321')
        ->call('save')
        ->assertHasNoErrors();

    $contact = Contact::firstWhere('name', 'Ana Torres');

    expect($contact->phones->map(fn (ContactPhone $phone) => [$phone->type, $phone->number])->all())
        ->toBe([[PhoneType::Office, '5512345678'], [PhoneType::Home, '5587654321']]);
});

test('phone rows can be added and removed from the repeater', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createContact')
        ->assertCount('phones', 1)
        ->call('addPhone')
        ->call('addPhone')
        ->assertCount('phones', 3)
        ->set('phones.1.number', 'second')
        ->call('removePhone', 0)
        ->assertCount('phones', 2)
        ->assertSet('phones.0.number', 'second');
});

test('blank phone rows are ignored when saving', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->call('addPhone')
        ->set('phones.1.number', '5512345678')
        ->call('save')
        ->assertHasNoErrors();

    expect(Contact::firstWhere('name', 'Ana Torres')->phones)->toHaveCount(1);
});

test('a phone needs a valid kind and a number of at most 50 characters', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createContact')
        ->set('organization_id', (string) $organization->id)
        ->set('name', 'Ana Torres')
        ->set('phones.0.type', 'fax')
        ->set('phones.0.number', str_repeat('5', 51))
        ->call('save')
        ->assertHasErrors(['phones.0.type' => Enum::class, 'phones.0.number' => 'max']);

    expect(Contact::count())->toBe(0);
});

test('editing a contact loads its phones and replaces them on save', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();
    ContactPhone::factory()->for($contact)->create(['type' => PhoneType::Mobile, 'number' => '5511111111']);
    ContactPhone::factory()->for($contact)->create(['type' => PhoneType::Home, 'number' => '5522222222']);

    Livewire::test(Index::class)
        ->call('editContact', $contact->id)
        ->assertSet('phones', [
            ['type' => 'mobile', 'number' => '5511111111'],
            ['type' => 'home', 'number' => '5522222222'],
        ])
        ->call('removePhone', 1)
        ->set('phones.0.number', '5533333333')
        ->call('save')
        ->assertHasNoErrors();

    expect($contact->phones()->pluck('number')->all())->toBe(['5533333333']);
});

test('the contacts list shows each phone with its kind', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();
    ContactPhone::factory()->for($contact)->create(['type' => PhoneType::Office, 'number' => '5599999999']);

    Livewire::test(Index::class)
        ->assertSee('Oficina:')
        ->assertSee('5599999999');
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
