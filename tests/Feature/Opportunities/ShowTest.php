<?php

use App\Enums\InteractionType;
use App\Livewire\Opportunities\Show;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;
use Livewire\Livewire;

test('the opportunity page requires authentication', function () {
    $opportunity = Opportunity::factory()->create();

    $this->get(route('opportunities.show', $opportunity))->assertRedirect(route('login'));
});

test('the opportunity page shows its details and interactions, newest first', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create(['title' => 'Estudio de opinión']);
    Interaction::factory()->for($opportunity)->create(['subject' => 'Primera llamada', 'occurred_at' => now()->subDay()]);
    Interaction::factory()->for($opportunity)->create(['subject' => 'Demo con dirección', 'occurred_at' => now()]);

    $this->get(route('opportunities.show', $opportunity))
        ->assertOk()
        ->assertSeeInOrder(['Estudio de opinión', 'Demo con dirección', 'Primera llamada']);
});

test('the opportunity page is not found when the opportunity or its organization is deleted', function () {
    $this->actingAs(User::factory()->create());

    $deleted = Opportunity::factory()->trashed()->create();
    $orphaned = Opportunity::factory()->for(Organization::factory()->trashed())->create();

    $this->get(route('opportunities.show', $deleted))->assertNotFound();
    $this->get(route('opportunities.show', $orphaned))->assertNotFound();
});

test('a user can move the opportunity to another stage', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $next = PipelineStage::factory()->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->set('pipeline_stage_id', (string) $next->id)
        ->assertHasNoErrors();

    expect($opportunity->refresh()->pipeline_stage_id)->toBe($next->id);
});

test('moving the opportunity to a non-existing stage is rejected', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $originalStageId = $opportunity->pipeline_stage_id;

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->set('pipeline_stage_id', '999')
        ->assertHasErrors('pipeline_stage_id');

    expect($opportunity->refresh()->pipeline_stage_id)->toBe($originalStageId);
});

test('a user can log an interaction linked to contacts of the organization', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $opportunity = Opportunity::factory()->create();
    $contacts = Contact::factory()->count(2)->for($opportunity->organization)->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('createInteraction')
        ->set('type', InteractionType::Meeting->value)
        ->set('subject', 'Reunión de arranque')
        ->set('occurred_at', '2026-09-15T10:30')
        ->set('contactIds', $contacts->pluck('id')->map(fn (int $id) => (string) $id)->all())
        ->call('saveInteraction')
        ->assertHasNoErrors();

    $interaction = $opportunity->interactions()->sole();

    expect($interaction->type)->toBe(InteractionType::Meeting)
        ->and($interaction->user_id)->toBe($user->id)
        ->and($interaction->subject)->toBe('Reunión de arranque')
        ->and($interaction->contacts->pluck('id')->sort()->values()->all())->toBe($contacts->pluck('id')->sort()->values()->all());
});

test('an interaction requires a subject and cannot include contacts of another organization', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $foreignContact = Contact::factory()->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->set('type', 'fax')
        ->set('contactIds', [(string) $foreignContact->id])
        ->call('saveInteraction')
        ->assertHasErrors(['type' => Enum::class, 'subject' => 'required', 'contactIds.0' => 'exists']);

    expect($opportunity->interactions()->count())->toBe(0);
});

test('an admin can delete an interaction and a non-admin cannot', function () {
    $opportunity = Opportunity::factory()->create();
    $interaction = Interaction::factory()->for($opportunity)->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('deleteInteraction', $interaction->id)
        ->assertForbidden();

    $this->assertModelExists($interaction);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('deleteInteraction', $interaction->id);

    $this->assertModelMissing($interaction);
});
