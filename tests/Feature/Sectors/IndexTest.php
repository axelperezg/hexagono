<?php

use App\Livewire\Sectors\Index;
use App\Models\Organization;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

test('the sectors module requires authentication', function () {
    $this->get(route('sectors.index'))->assertRedirect(route('login'));
});

test('it lists sectors and filters them by search term', function () {
    $this->actingAs(User::factory()->create());

    Sector::factory()->create(['name' => 'Gobierno']);
    Sector::factory()->create(['name' => 'Medios']);

    Livewire::test(Index::class)
        ->assertSee('Gobierno')
        ->assertSee('Medios')
        ->set('search', 'gob')
        ->assertSee('Gobierno')
        ->assertDontSee('Medios');
});

test('a user can create a sector', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('createSector')
        ->set('name', 'Gobierno')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('sectors', ['name' => 'Gobierno']);
});

test('the sector name is required and must be unique', function () {
    $this->actingAs(User::factory()->create());
    Sector::factory()->create(['name' => 'Gobierno']);

    Livewire::test(Index::class)
        ->call('createSector')
        ->call('save')
        ->assertHasErrors(['name' => 'required'])
        ->set('name', 'Gobierno')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('a user can edit a sector keeping its own name', function () {
    $this->actingAs(User::factory()->create());
    $sector = Sector::factory()->create(['name' => 'Gobierno']);

    Livewire::test(Index::class)
        ->call('editSector', $sector->id)
        ->assertSet('name', 'Gobierno')
        ->call('save')
        ->assertHasNoErrors()
        ->call('editSector', $sector->id)
        ->set('name', 'Sector público')
        ->call('save')
        ->assertHasNoErrors();

    expect($sector->refresh()->name)->toBe('Sector público');
});

test('an admin can delete a sector that is not in use', function () {
    $this->actingAs(User::factory()->admin()->create());
    $sector = Sector::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $sector->id)
        ->call('delete');

    $this->assertModelMissing($sector);
});

test('a sector in use by an organization cannot be deleted', function () {
    $this->actingAs(User::factory()->admin()->create());
    $sector = Sector::factory()->create();
    Organization::factory()->for($sector)->create()->delete();

    Livewire::test(Index::class)
        ->call('confirmDelete', $sector->id)
        ->call('delete');

    $this->assertModelExists($sector);
});

test('a non-admin user cannot delete a sector', function () {
    $this->actingAs(User::factory()->create());
    $sector = Sector::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $sector->id)
        ->assertForbidden();

    $this->assertModelExists($sector);
});
