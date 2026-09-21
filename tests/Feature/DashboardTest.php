<?php

use App\Livewire\Dashboard;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee(asset('logo-ci.png'), false);
});

test('the dashboard defaults to the current fiscal year and counts only that year\'s opportunities', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->count(2)->create(['fiscal_year' => now()->year]);
    Opportunity::factory()->create(['fiscal_year' => 2030]);

    $component = Livewire::test(Dashboard::class)
        ->assertSet('fiscalYearFilter', (string) now()->year);

    expect($component->instance()->opportunityStats()['total'])->toBe(2);

    $component->set('fiscalYearFilter', '2030');

    expect($component->instance()->opportunityStats()['total'])->toBe(1);
});

test('the dashboard counts the won opportunities of the fiscal year', function () {
    $this->actingAs(User::factory()->create());
    $won = PipelineStage::factory()->create(['is_won' => true]);
    $open = PipelineStage::factory()->create();
    Opportunity::factory()->for($won, 'stage')->create(['fiscal_year' => now()->year]);
    Opportunity::factory()->for($open, 'stage')->create(['fiscal_year' => now()->year]);
    Opportunity::factory()->for($won, 'stage')->create(['fiscal_year' => 2030]);

    $stats = Livewire::test(Dashboard::class)->instance()->opportunityStats();

    expect($stats['total'])->toBe(2)
        ->and($stats['won'])->toBe(1);
});

test('opportunities are grouped by the month of their expected close date', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-03-10']);
    Opportunity::factory()->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-03-25']);
    Opportunity::factory()->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-11-02']);
    Opportunity::factory()->create(['fiscal_year' => 2028, 'expected_close_date' => null]);
    Opportunity::factory()->create(['fiscal_year' => 2028, 'expected_close_date' => '2029-01-15']);

    $stats = Livewire::test(Dashboard::class)
        ->set('fiscalYearFilter', '2028')
        ->instance()->opportunityStats();

    expect($stats['byMonth'][3])->toBe(2)
        ->and($stats['byMonth'][11])->toBe(1)
        ->and(array_sum($stats['byMonth']))->toBe(3)
        ->and($stats['outsideYear'])->toBe(2);
});

test('opportunities of deleted organizations are left out of the dashboard', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['fiscal_year' => now()->year]);
    Opportunity::factory()->for(Organization::factory()->trashed())->create(['fiscal_year' => now()->year]);

    expect(Livewire::test(Dashboard::class)->instance()->opportunityStats()['total'])->toBe(1);
});

test('pending tasks are grouped by the month of their end date and overdue ones are counted', function () {
    $this->travelTo('2026-06-15');
    $this->actingAs(User::factory()->create());
    Task::factory()->withoutOpportunity()->create(['start_date' => '2026-05-01', 'end_date' => '2026-06-01']);
    Task::factory()->create(['start_date' => '2026-05-01', 'end_date' => '2026-06-30']);
    Task::factory()->withoutOpportunity()->create(['start_date' => '2026-05-01', 'end_date' => '2026-09-10']);
    Task::factory()->completed()->create(['end_date' => '2026-06-20']);
    Task::factory()->create(['end_date' => '2027-06-20']);
    Task::factory()->for(Opportunity::factory()->for(Organization::factory()->trashed()), 'opportunity')->create(['end_date' => '2026-06-20']);

    $stats = Livewire::test(Dashboard::class)->instance()->taskStats();

    expect($stats['total'])->toBe(3)
        ->and($stats['overdue'])->toBe(1)
        ->and($stats['byMonth'][6])->toBe(2)
        ->and($stats['byMonth'][9])->toBe(1);
});

test('a fiscal year outside the supported range falls back to the current year', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['fiscal_year' => now()->year]);

    $component = Livewire::withQueryParams(['ejercicio' => 'abc'])->test(Dashboard::class);

    expect($component->instance()->fiscalYear())->toBe(now()->year)
        ->and($component->instance()->opportunityStats()['total'])->toBe(1);
});

test('the dashboard renders the cards and both charts', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['fiscal_year' => now()->year, 'expected_close_date' => now()->startOfYear()->addMonths(2)->toDateString()]);

    Livewire::test(Dashboard::class)
        ->assertSee('Oportunidades ganadas')
        ->assertSee('Oportunidades por mes')
        ->assertSee('Tareas pendientes')
        ->assertSee('Marzo: 1 oportunidad', false);
});

test('amounts only add up MXN opportunities, in total, when won and by month', function () {
    $this->actingAs(User::factory()->create());
    $won = PipelineStage::factory()->create(['is_won' => true]);
    $open = PipelineStage::factory()->create();
    Opportunity::factory()->for($won, 'stage')->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-03-10', 'estimated_amount' => 1000, 'currency' => 'MXN']);
    Opportunity::factory()->for($open, 'stage')->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-03-20', 'estimated_amount' => 500.5, 'currency' => 'MXN']);
    Opportunity::factory()->for($open, 'stage')->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-04-01', 'estimated_amount' => null, 'currency' => 'MXN']);
    Opportunity::factory()->for($won, 'stage')->create(['fiscal_year' => 2028, 'expected_close_date' => '2028-03-15', 'estimated_amount' => 9999, 'currency' => 'USD']);

    $stats = Livewire::test(Dashboard::class)
        ->set('fiscalYearFilter', '2028')
        ->instance()->opportunityStats();

    expect($stats['amount'])->toBe(1500.5)
        ->and($stats['wonAmount'])->toBe(1000.0)
        ->and($stats['amountByMonth'][3])->toBe(1500.5)
        ->and($stats['amountByMonth'][4])->toBe(0.0)
        ->and($stats['otherCurrency'])->toBe(1);
});

test('the dashboard shows the MXN amounts and warns about opportunities in another currency', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['fiscal_year' => now()->year, 'estimated_amount' => 250000, 'currency' => 'MXN']);
    Opportunity::factory()->create(['fiscal_year' => now()->year, 'estimated_amount' => 100, 'currency' => 'USD']);

    Livewire::test(Dashboard::class)
        ->assertSee('Monto estimado por mes')
        ->assertSee('$250,000')
        ->assertSee('1 oportunidad en otra moneda no se incluye en los montos.');
});

test('the funnel lists every stage in pipeline order with the opportunities of the fiscal year and their MXN amount', function () {
    $this->actingAs(User::factory()->create());
    $proposal = PipelineStage::factory()->create(['name' => 'Propuesta', 'position' => 2]);
    $prospect = PipelineStage::factory()->create(['name' => 'Prospecto', 'position' => 1]);
    $won = PipelineStage::factory()->create(['name' => 'Cerrada', 'position' => 3, 'is_won' => true]);
    Opportunity::factory()->count(2)->for($prospect, 'stage')->create(['fiscal_year' => 2028, 'estimated_amount' => 1000, 'currency' => 'MXN']);
    Opportunity::factory()->for($proposal, 'stage')->create(['fiscal_year' => 2028, 'estimated_amount' => 500, 'currency' => 'USD']);
    Opportunity::factory()->for($prospect, 'stage')->create(['fiscal_year' => 2029]);

    $dashboard = Livewire::test(Dashboard::class)
        ->set('fiscalYearFilter', '2028')
        ->instance();
    $funnel = $dashboard->funnel($dashboard->opportunityStats()['byStage']);

    expect(array_column($funnel, 'name'))->toBe(['Prospecto', 'Propuesta', 'Cerrada'])
        ->and(array_column($funnel, 'count'))->toBe([2, 1, 0])
        ->and(array_column($funnel, 'amount'))->toBe([2000.0, 0.0, 0.0])
        ->and(array_column($funnel, 'isWon'))->toBe([false, false, true]);
});

test('the dashboard renders the funnel with the stage names and counts', function () {
    $this->actingAs(User::factory()->create());
    $stage = PipelineStage::factory()->create(['name' => 'Demo', 'position' => 1]);
    Opportunity::factory()->for($stage, 'stage')->create(['fiscal_year' => now()->year, 'estimated_amount' => 250000, 'currency' => 'MXN']);

    Livewire::test(Dashboard::class)
        ->assertSee('Embudo por etapa')
        ->assertSee('Demo: 1 oportunidad · $250,000 MXN', false);
});
