<?php

use App\Livewire\Opportunities\Index;
use App\Livewire\Opportunities\Show;
use App\Models\Opportunity;
use App\Models\OpportunityStageChange;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Livewire\Livewire;

test('creating an opportunity records its initial stage', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $stage = PipelineStage::factory()->create(['position' => 1]);
    $organization = Organization::factory()->create();

    Livewire::test(Index::class)
        ->call('createOpportunity')
        ->set('organization_id', (string) $organization->id)
        ->set('title', 'Estudio de opinión')
        ->call('save');

    $change = OpportunityStageChange::sole();

    expect($change->from_stage_id)->toBeNull()
        ->and($change->to_stage_id)->toBe($stage->id)
        ->and($change->user_id)->toBe($user->id);
});

test('moving an opportunity to another stage records who moved it and from where', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $opportunity = Opportunity::factory()->create();
    $initialStageId = $opportunity->pipeline_stage_id;
    $next = PipelineStage::factory()->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->set('pipeline_stage_id', (string) $next->id);

    $latest = $opportunity->stageChanges()->latest('id')->first();

    expect($opportunity->stageChanges()->count())->toBe(2)
        ->and($latest->from_stage_id)->toBe($initialStageId)
        ->and($latest->to_stage_id)->toBe($next->id)
        ->and($latest->user_id)->toBe($user->id);
});

test('updating an opportunity without changing its stage records nothing', function () {
    $opportunity = Opportunity::factory()->create();

    $opportunity->update(['title' => 'Nuevo título']);

    expect($opportunity->stageChanges()->count())->toBe(1);
});

test('the opportunity page lists the stage history newest first', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $opportunity->stageChanges()->delete();
    $prospect = PipelineStage::factory()->create(['name' => 'Prospecto inicial']);
    $demo = PipelineStage::factory()->create(['name' => 'Etapa de demo']);
    OpportunityStageChange::factory()->for($opportunity)->create(['from_stage_id' => null, 'to_stage_id' => $prospect->id, 'created_at' => now()->subDay()]);
    OpportunityStageChange::factory()->for($opportunity)->create(['from_stage_id' => $prospect->id, 'to_stage_id' => $demo->id, 'created_at' => now()]);

    $this->get(route('opportunities.show', $opportunity))
        ->assertOk()
        ->assertSeeInOrder(['Historial de etapas', 'Prospecto inicial', 'Etapa de demo', 'Creada en', 'Prospecto inicial']);
});
