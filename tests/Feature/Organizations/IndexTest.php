<?php

use App\Livewire\Organizations\Index;
use App\Models\Organization;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the organizations module requires authentication', function () {
    $this->get(route('organizations.index'))->assertRedirect(route('login'));
});

test('it lists organizations and filters them by search term', function () {
    $this->actingAs(User::factory()->create());

    Organization::factory()->for(Sector::factory()->create(['name' => 'Gobierno']))->create(['name' => 'Secretaría de Salud']);
    Organization::factory()->for(Sector::factory()->create(['name' => 'Medios']))->create(['name' => 'Editorial Norte']);

    Livewire::test(Index::class)
        ->assertSee('Secretaría de Salud')
        ->assertSee('Editorial Norte')
        ->set('search', 'salud')
        ->assertSee('Secretaría de Salud')
        ->assertDontSee('Editorial Norte');
});

test('a user can create an organization', function () {
    $this->actingAs(User::factory()->create());

    $sector = Sector::factory()->create(['name' => 'Gobierno']);

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('acronym', 'SSA')
        ->set('sectorId', (string) $sector->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('organizations', ['name' => 'Secretaría de Salud', 'acronym' => 'SSA', 'sector_id' => $sector->id, 'website' => null]);
});

test('the organization name is required and the website must be a url', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('website', 'not a url')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'website' => 'url']);
});

test('the acronym is optional and limited to 50 characters', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('acronym', str_repeat('A', 51))
        ->call('save')
        ->assertHasErrors(['acronym' => 'max'])
        ->set('acronym', '')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('organizations', ['name' => 'Secretaría de Salud', 'acronym' => null]);
});

test('it finds an organization by its acronym', function () {
    $this->actingAs(User::factory()->create());

    // Sector is left empty so a random factory sector can't match the search.
    Organization::factory()->create(['name' => 'Secretaría de Salud', 'acronym' => 'SSA', 'sector_id' => null]);
    Organization::factory()->create(['name' => 'Editorial Norte', 'acronym' => 'EDN', 'sector_id' => null]);

    Livewire::test(Index::class)
        ->set('search', 'ssa')
        ->assertSee('Secretaría de Salud')
        ->assertDontSee('Editorial Norte');
});

test('the sector must exist and is optional', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Sin sector')
        ->set('sectorId', '999')
        ->call('save')
        ->assertHasErrors(['sectorId' => 'exists']);

    $component->set('sectorId', '')->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('organizations', ['name' => 'Sin sector', 'sector_id' => null]);
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

test('a user can upload a logo for an organization', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $organization = Organization::firstWhere('name', 'Secretaría de Salud');

    expect($organization->logo_path)->toStartWith('organization-logos/')
        ->and($organization->logoUrl())->not->toBeNull();
    Storage::disk('public')->assertExists($organization->logo_path);
});

test('the logo must be a png, jpg or webp image of at most 2 MB', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(Index::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud');

    $invalid = [
        UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        UploadedFile::fake()->image('logo.png')->size(2049),
    ];

    foreach ($invalid as $file) {
        $component->set('logo', $file)->call('save')->assertHasErrors(['logo']);
    }

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('replacing a logo deletes the previous file', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $oldPath = UploadedFile::fake()->image('old.png')->store('organization-logos', 'public');
    $organization = Organization::factory()->create(['logo_path' => $oldPath]);

    Livewire::test(Index::class)
        ->call('editOrganization', $organization->id)
        ->set('logo', UploadedFile::fake()->image('new.png'))
        ->call('save')
        ->assertHasNoErrors();

    $organization->refresh();

    expect($organization->logo_path)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($organization->logo_path);
});

test('editing an organization without a new upload keeps its logo', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $path = UploadedFile::fake()->image('logo.png')->store('organization-logos', 'public');
    $organization = Organization::factory()->create(['logo_path' => $path]);

    Livewire::test(Index::class)
        ->call('editOrganization', $organization->id)
        ->set('name', 'New name')
        ->call('save')
        ->assertHasNoErrors();

    expect($organization->refresh()->logo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('a user can remove the logo of an organization', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $path = UploadedFile::fake()->image('logo.png')->store('organization-logos', 'public');
    $organization = Organization::factory()->create(['logo_path' => $path]);

    Livewire::test(Index::class)
        ->call('editOrganization', $organization->id)
        ->set('removeLogo', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($organization->refresh()->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('organizations no longer offer tags in the list, the filter or the form', function () {
    $this->actingAs(User::factory()->create());
    Organization::factory()->create();

    Livewire::test(Index::class)
        ->assertDontSee('Etiqueta')
        ->assertDontSee('etiquetas');
});
