<?php

use App\Livewire\Opportunities\Index;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Livewire\Livewire;

test('the opportunities module requires authentication', function () {
    $this->get(route('opportunities.index'))->assertRedirect(route('login'));
});

test('it lists opportunities and filters them by search term and stage', function () {
    $this->actingAs(User::factory()->create());

    $demo = PipelineStage::factory()->create(['name' => 'Demo']);
    $proposal = PipelineStage::factory()->create(['name' => 'Propuesta']);
    Opportunity::factory()->for($demo, 'stage')->create(['title' => 'Estudio de opinión']);
    Opportunity::factory()->for($proposal, 'stage')->create(['title' => 'Monitoreo de medios']);

    Livewire::test(Index::class)
        ->assertSee('Estudio de opinión')
        ->assertSee('Monitoreo de medios')
        ->set('search', 'monitoreo')
        ->assertDontSee('Estudio de opinión')
        ->set('search', '')
        ->set('stageFilter', (string) $demo->id)
        ->assertSee('Estudio de opinión')
        ->assertDontSee('Monitoreo de medios');
});

test('a user can create an opportunity owned by themselves in the first stage by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $first = PipelineStage::factory()->create(['position' => 1]);
    PipelineStage::factory()->create(['position' => 2]);
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->assertSet('pipeline_stage_id', (string) $first->id)
        ->assertSet('user_id', (string) $user->id)
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Estudio de opinión')
        ->set('estimated_amount', '150000.50')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('opportunities', [
        'organization_id' => $organization->id,
        'pipeline_stage_id' => $first->id,
        'user_id' => $user->id,
        'title' => 'Estudio de opinión',
        'estimated_amount' => '150000.50',
        'currency' => 'MXN',
    ]);
});

test('the opportunity requires a title, an organization and a supported currency', function () {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('currency', 'XXX')
        ->set('estimated_amount', '-5')
        ->call('save')
        ->assertHasErrors(['title' => 'required', 'organization_id' => 'required', 'currency' => 'in', 'estimated_amount' => 'min']);
});

test('a user can edit an opportunity', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create(['title' => 'Old title']);

    Livewire::test(Index::class)
        ->call('editOpportunity', $opportunity->id)
        ->assertSet('title', 'Old title')
        ->set('title', 'New title')
        ->call('save')
        ->assertHasNoErrors();

    expect($opportunity->refresh()->title)->toBe('New title');
});

test('an admin can delete an opportunity and a non-admin cannot', function () {
    $opportunity = Opportunity::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('confirmDelete', $opportunity->id)
        ->assertForbidden();

    $this->assertNotSoftDeleted($opportunity);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Index::class)
        ->call('confirmDelete', $opportunity->id)
        ->call('delete');

    $this->assertSoftDeleted($opportunity);
});

test('the list is filtered by the current fiscal year by default and can be switched to another year', function () {
    $this->actingAs(User::factory()->create());

    Opportunity::factory()->create(['title' => 'Estudio de este año', 'fiscal_year' => now()->year]);
    Opportunity::factory()->create(['title' => 'Estudio de 2030', 'fiscal_year' => 2030]);

    Livewire::test(Index::class)
        ->assertSet('fiscalYearFilter', (string) now()->year)
        ->assertSee('Estudio de este año')
        ->assertDontSee('Estudio de 2030')
        ->set('fiscalYearFilter', '2030')
        ->assertSee('Estudio de 2030')
        ->assertDontSee('Estudio de este año');
});

test('the form defaults the fiscal year to the current year and saves the chosen one', function () {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create();
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->assertSet('fiscal_year', (string) now()->year)
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Estudio 2031')
        ->set('fiscal_year', '2031')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('opportunities', ['title' => 'Estudio 2031', 'fiscal_year' => 2031]);
});

test('the fiscal year must be between 2026 and 2036', function (string $year) {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('fiscal_year', $year)
        ->call('save')
        ->assertHasErrors(['fiscal_year']);
})->with(['2025', '2037', 'abc']);
