<?php

use App\Enums\BudgetItem;
use App\Enums\Priority;
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

test('an opportunity can store its budget item, campaign, version and priority', function () {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create(['position' => 1]);
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Estudio de opinión')
        ->set('budget_item', '36101')
        ->set('campaign', 'Campaña de verano')
        ->set('version', 'V2')
        ->set('priority', 'high')
        ->call('save')
        ->assertHasNoErrors();

    $opportunity = Opportunity::firstWhere('title', 'Estudio de opinión');

    expect($opportunity->budget_item)->toBe(BudgetItem::Item36101)
        ->and($opportunity->campaign)->toBe('Campaña de verano')
        ->and($opportunity->version)->toBe('V2')
        ->and($opportunity->priority)->toBe(Priority::High);
});

test('the budget item, campaign, version and priority are optional', function () {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create(['position' => 1]);
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Sin datos extra')
        ->call('save')
        ->assertHasNoErrors();

    $opportunity = Opportunity::firstWhere('title', 'Sin datos extra');

    expect($opportunity->budget_item)->toBeNull()
        ->and($opportunity->campaign)->toBeNull()
        ->and($opportunity->version)->toBeNull()
        ->and($opportunity->priority)->toBeNull();
});

test('the budget item and priority must be one of the supported values', function () {
    $this->actingAs(User::factory()->create());
    PipelineStage::factory()->create(['position' => 1]);

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('budget_item', '99999')
        ->set('priority', 'urgent')
        ->call('save')
        ->assertHasErrors(['budget_item', 'priority']);
});

test('editing an opportunity loads and changes its priority and budget item', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create(['priority' => Priority::Low, 'budget_item' => BudgetItem::NotApplicable]);

    Livewire::test(Index::class)
        ->call('editOpportunity', $opportunity->id)
        ->assertSet('priority', 'low')
        ->assertSet('budget_item', 'no_aplica')
        ->set('priority', 'medium')
        ->set('budget_item', '36201')
        ->call('save')
        ->assertHasNoErrors();

    expect($opportunity->refresh()->priority)->toBe(Priority::Medium)
        ->and($opportunity->budget_item)->toBe(BudgetItem::Item36201);
});

test('opportunities show their priority and can be filtered by it', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['title' => 'Urgente', 'priority' => Priority::High]);
    Opportunity::factory()->create(['title' => 'Tranquila', 'priority' => Priority::Low]);
    Opportunity::factory()->create(['title' => 'Sin prioridad definida']);

    Livewire::test(Index::class)
        ->assertSee('Prioridad alta')
        ->assertSee('Prioridad baja')
        ->set('priorityFilter', 'high')
        ->assertSee('Urgente')
        ->assertDontSee('Tranquila')
        ->assertDontSee('Sin prioridad definida');
});
