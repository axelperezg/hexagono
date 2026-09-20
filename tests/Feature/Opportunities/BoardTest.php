<?php

use App\Livewire\Opportunities\Board;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('the board requires authentication', function () {
    $this->get(route('opportunities.board'))->assertRedirect(route('login'));
});

test('the board is displayed to an authenticated user and does not clash with the detail route', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('opportunities.board'))->assertOk()->assertSee('Tablero de oportunidades');
});

test('it shows one column per stage with its opportunities and per-currency totals', function () {
    $this->actingAs(User::factory()->create());
    $prospect = PipelineStage::factory()->create(['name' => 'Prospecto', 'position' => 1]);
    $demo = PipelineStage::factory()->create(['name' => 'Demo', 'position' => 2]);
    Opportunity::factory()->for($prospect, 'stage')->create(['title' => 'Estudio A', 'estimated_amount' => 1000, 'currency' => 'MXN']);
    Opportunity::factory()->for($prospect, 'stage')->create(['title' => 'Estudio B', 'estimated_amount' => 500.5, 'currency' => 'MXN']);
    Opportunity::factory()->for($prospect, 'stage')->create(['title' => 'Estudio C', 'estimated_amount' => 200, 'currency' => 'USD']);
    Opportunity::factory()->for($demo, 'stage')->create(['title' => 'Estudio D']);

    Livewire::test(Board::class)
        ->assertSeeInOrder(['Prospecto', '$1,500.50 MXN', '$200.00 USD', 'Estudio A', 'Demo', 'Estudio D'])
        ->assertSee('Estudio B')
        ->assertSee('Estudio C');
});

test('it hides opportunities of deleted organizations', function () {
    $this->actingAs(User::factory()->create());
    Opportunity::factory()->create(['title' => 'Visible']);
    Opportunity::factory()->for(Organization::factory()->trashed())->create(['title' => 'Huérfana']);

    Livewire::test(Board::class)->assertSee('Visible')->assertDontSee('Huérfana');
});

test('it filters cards by search term and by owner', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Opportunity::factory()->for($user, 'owner')->create(['title' => 'Estudio propio']);
    Opportunity::factory()->create(['title' => 'Monitoreo ajeno']);

    Livewire::test(Board::class)
        ->set('search', 'monitoreo')
        ->assertSee('Monitoreo ajeno')
        ->assertDontSee('Estudio propio')
        ->set('search', '')
        ->set('owner', 'mine')
        ->assertSee('Estudio propio')
        ->assertDontSee('Monitoreo ajeno');
});

test('dropping a card in another column changes its stage and records it in the history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $opportunity = Opportunity::factory()->create();
    $target = PipelineStage::factory()->create();

    Livewire::test(Board::class)
        ->call('moveOpportunity', $opportunity->id, 0, $target->id)
        ->assertHasNoErrors();

    $latest = $opportunity->refresh()->stageChanges()->latest('id')->first();

    expect($opportunity->pipeline_stage_id)->toBe($target->id)
        ->and($latest->to_stage_id)->toBe($target->id)
        ->and($latest->user_id)->toBe($user->id);
});

test('dropping a card at a position keeps the order of the destination column', function () {
    $this->actingAs(User::factory()->create());
    $stage = PipelineStage::factory()->create();
    [$first, $second, $third] = Opportunity::factory()->count(3)->for($stage, 'stage')
        ->sequence(['board_position' => 0], ['board_position' => 1], ['board_position' => 2])
        ->create();
    $incoming = Opportunity::factory()->create();

    Livewire::test(Board::class)->call('moveOpportunity', $incoming->id, 1, $stage->id);

    $ordered = Opportunity::where('pipeline_stage_id', $stage->id)->orderBy('board_position')->pluck('id')->all();

    expect($ordered)->toBe([$first->id, $incoming->id, $second->id, $third->id]);
});

test('reordering a card inside its column persists without adding history', function () {
    $this->actingAs(User::factory()->create());
    $stage = PipelineStage::factory()->create();
    [$first, $second, $third] = Opportunity::factory()->count(3)->for($stage, 'stage')
        ->sequence(['board_position' => 0], ['board_position' => 1], ['board_position' => 2])
        ->create();

    Livewire::test(Board::class)->call('moveOpportunity', $third->id, 0, $stage->id);

    $ordered = Opportunity::where('pipeline_stage_id', $stage->id)->orderBy('board_position')->pluck('id')->all();

    expect($ordered)->toBe([$third->id, $first->id, $second->id])
        ->and($third->stageChanges()->count())->toBe(1);
});

test('moving to a non-existing stage or a non-existing opportunity is rejected', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $originalStageId = $opportunity->pipeline_stage_id;

    expect(fn () => Livewire::test(Board::class)->call('moveOpportunity', $opportunity->id, 0, 999))
        ->toThrow(ModelNotFoundException::class)
        ->and(fn () => Livewire::test(Board::class)->call('moveOpportunity', 999, 0, $originalStageId))
        ->toThrow(ModelNotFoundException::class);

    expect($opportunity->refresh()->pipeline_stage_id)->toBe($originalStageId);
});
