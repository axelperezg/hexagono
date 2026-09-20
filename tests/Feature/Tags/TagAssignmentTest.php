<?php

use App\Livewire\Opportunities\Index as OpportunitiesIndex;
use App\Livewire\Organizations\Index as OrganizationsIndex;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

test('an organization can be tagged with existing and new tags', function () {
    $this->actingAs(User::factory()->create());
    $existing = Tag::factory()->create(['name' => 'Gobierno']);

    Livewire::test(OrganizationsIndex::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('tagIds', [(string) $existing->id])
        ->set('newTags', 'Referido, Prioridad alta')
        ->call('save')
        ->assertHasNoErrors();

    $organization = Organization::firstWhere('name', 'Secretaría de Salud');

    expect($organization->tags->pluck('name')->all())->toBe(['Gobierno', 'Prioridad alta', 'Referido']);
});

test('new tag names reuse an existing tag regardless of case and are not duplicated', function () {
    $this->actingAs(User::factory()->create());
    Tag::factory()->create(['name' => 'Referido']);

    Livewire::test(OrganizationsIndex::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('newTags', 'referido, REFERIDO')
        ->call('save')
        ->assertHasNoErrors();

    expect(Tag::count())->toBe(1)
        ->and(Organization::firstWhere('name', 'Secretaría de Salud')->tags)->toHaveCount(1);
});

test('editing an organization replaces its tags', function () {
    $this->actingAs(User::factory()->create());
    $old = Tag::factory()->create(['name' => 'Alfa']);
    $kept = Tag::factory()->create(['name' => 'Beta']);
    $organization = Organization::factory()->create();
    $organization->tags()->attach([$old->id, $kept->id]);

    Livewire::test(OrganizationsIndex::class)
        ->call('editOrganization', $organization->id)
        ->assertSet('tagIds', [(string) $old->id, (string) $kept->id])
        ->set('tagIds', [(string) $kept->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($organization->tags()->pluck('tags.id')->all())->toBe([$kept->id]);
});

test('tagging rejects a tag that does not exist', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(OrganizationsIndex::class)
        ->call('createOrganization')
        ->set('name', 'Secretaría de Salud')
        ->set('tagIds', ['999'])
        ->call('save')
        ->assertHasErrors('tagIds.0');
});

test('organizations can be filtered by tag', function () {
    $this->actingAs(User::factory()->create());
    $tag = Tag::factory()->create();
    Organization::factory()->create(['name' => 'Con etiqueta'])->tags()->attach($tag);
    Organization::factory()->create(['name' => 'Sin etiqueta']);

    Livewire::test(OrganizationsIndex::class)
        ->assertSee('Sin etiqueta')
        ->set('tagFilter', (string) $tag->id)
        ->assertSee('Con etiqueta')
        ->assertDontSee('Sin etiqueta');
});

test('an opportunity can be tagged and filtered by tag', function () {
    $this->actingAs(User::factory()->create());
    $stage = PipelineStage::factory()->create(['position' => 1]);
    $organization = Organization::factory()->create();

    Livewire::test(OpportunitiesIndex::class)
        ->call('createOpportunity')
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Estudio etiquetado')
        ->set('newTags', 'Licitación')
        ->call('save')
        ->assertHasNoErrors();

    Opportunity::factory()->for($stage, 'stage')->create(['title' => 'Estudio sin etiqueta']);

    $opportunity = Opportunity::firstWhere('title', 'Estudio etiquetado');

    expect($opportunity->tags->pluck('name')->all())->toBe(['Licitación']);

    Livewire::test(OpportunitiesIndex::class)
        ->set('tagFilter', (string) Tag::sole()->id)
        ->assertSee('Estudio etiquetado')
        ->assertDontSee('Estudio sin etiqueta');
});
