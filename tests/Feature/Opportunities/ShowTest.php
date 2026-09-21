<?php

use App\Enums\InteractionType;
use App\Livewire\Opportunities\Show;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

test('a user can add a task assigned to themselves by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('createTask')
        ->assertSet('task_user_id', (string) $user->id)
        ->set('task_title', 'Enviar propuesta')
        ->set('task_due_date', '2026-10-01')
        ->call('saveTask')
        ->assertHasNoErrors();

    $task = $opportunity->tasks()->sole();

    expect($task->title)->toBe('Enviar propuesta')
        ->and($task->due_date->toDateString())->toBe('2026-10-01')
        ->and($task->user_id)->toBe($user->id)
        ->and($task->notes)->toBeNull();
});

test('a task requires a title and a due date', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('createTask')
        ->call('saveTask')
        ->assertHasErrors(['task_title' => 'required', 'task_due_date' => 'required']);

    expect($opportunity->tasks()->count())->toBe(0);
});

test('a user can edit, complete and reopen a task of the opportunity', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $task = Task::factory()->for($opportunity)->create(['title' => 'Old title']);

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('editTask', $task->id)
        ->assertSet('task_title', 'Old title')
        ->set('task_title', 'New title')
        ->call('saveTask')
        ->assertHasNoErrors()
        ->call('toggleTask', $task->id);

    expect($task->refresh()->title)->toBe('New title')
        ->and($task->isCompleted())->toBeTrue();

    Livewire::test(Show::class, ['opportunity' => $opportunity])->call('toggleTask', $task->id);

    expect($task->refresh()->isCompleted())->toBeFalse();
});

test('tasks of another opportunity cannot be touched from this page', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();
    $foreignTask = Task::factory()->create();

    expect(fn () => Livewire::test(Show::class, ['opportunity' => $opportunity])->call('toggleTask', $foreignTask->id))
        ->toThrow(ModelNotFoundException::class);

    expect($foreignTask->refresh()->isCompleted())->toBeFalse();
});

test('an admin can delete a task and a non-admin cannot', function () {
    $opportunity = Opportunity::factory()->create();
    $task = Task::factory()->for($opportunity)->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('deleteTask', $task->id)
        ->assertForbidden();

    $this->assertModelExists($task);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Show::class, ['opportunity' => $opportunity])
        ->call('deleteTask', $task->id);

    $this->assertModelMissing($task);
});

test('the opportunity page shows its fiscal year and the organization logo', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create(['logo_path' => 'organization-logos/acme.png']);
    $opportunity = Opportunity::factory()->for($organization)->create(['fiscal_year' => 2031]);

    $this->get(route('opportunities.show', $opportunity))
        ->assertOk()
        ->assertSeeInOrder(['Ejercicio fiscal', '2031'])
        ->assertSee($organization->logoUrl(), false);
});
