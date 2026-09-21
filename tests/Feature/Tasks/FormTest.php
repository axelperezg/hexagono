<?php

use App\Livewire\Tasks\Form;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('a user can create a task without an opportunity, assigned to themselves and starting today by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Form::class)
        ->call('createTask')
        ->assertSet('user_id', (string) $user->id)
        ->assertSet('start_date', today()->format('Y-m-d'))
        ->assertSet('hasOpportunity', false)
        ->set('concept', 'Preparar informe')
        ->set('end_date', today()->addWeek()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('task-saved');

    $task = Task::sole();

    expect($task->concept)->toBe('Preparar informe')
        ->and($task->opportunity_id)->toBeNull()
        ->and($task->user_id)->toBe($user->id)
        ->and($task->notes)->toBeNull();
});

test('a task can be related to an opportunity, and mounting with one pre-links it', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Form::class, ['opportunityId' => $opportunity->id])
        ->call('createTask')
        ->assertSet('hasOpportunity', true)
        ->assertSet('opportunity_id', (string) $opportunity->id)
        ->set('concept', 'Enviar propuesta')
        ->set('end_date', today()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Task::sole()->opportunity_id)->toBe($opportunity->id);
});

test('an opportunity is required only when the task is marked as related to one, and unticking it unlinks the task', function () {
    $this->actingAs(User::factory()->create());
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Form::class)
        ->call('createTask')
        ->set('concept', 'Tarea')
        ->set('end_date', today()->format('Y-m-d'))
        ->set('hasOpportunity', true)
        ->call('save')
        ->assertHasErrors(['opportunity_id' => 'required'])
        ->set('opportunity_id', (string) $opportunity->id)
        ->set('hasOpportunity', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Task::sole()->opportunity_id)->toBeNull();
});

test('a task requires a concept and dates, and the end date cannot precede the start date', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class)
        ->call('createTask')
        ->set('start_date', '')
        ->call('save')
        ->assertHasErrors(['concept' => 'required', 'start_date' => 'required', 'end_date' => 'required'])
        ->set('concept', 'Tarea')
        ->set('start_date', '2026-10-10')
        ->set('end_date', '2026-10-01')
        ->call('save')
        ->assertHasErrors(['end_date' => 'after_or_equal']);

    expect(Task::count())->toBe(0);
});

test('the repeater saves actions with their dates, drops blank rows and validates the rest', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class)
        ->call('createTask')
        ->set('concept', 'Tarea')
        ->set('end_date', today()->addDay()->format('Y-m-d'))
        ->call('addAction')
        ->call('addAction')
        ->call('addAction')
        ->set('taskActions.0.description', 'Llamada inicial')
        ->set('taskActions.0.performed_at', '2026-10-02')
        ->set('taskActions.2.description', 'Envío de correo')
        ->set('taskActions.2.performed_at', '')
        ->call('save')
        ->assertHasErrors(['taskActions.1.performed_at' => 'required'])
        ->set('taskActions.1.performed_at', '2026-10-05')
        ->call('save')
        ->assertHasNoErrors();

    expect(Task::sole()->actions->map(fn ($action) => [$action->description, $action->performed_at->toDateString()])->all())
        ->toBe([['Llamada inicial', '2026-10-02'], ['Envío de correo', '2026-10-05']]);
});

test('a user can edit a task, replacing its actions', function () {
    $this->actingAs(User::factory()->create());
    $task = Task::factory()->create(['concept' => 'Old concept']);
    $task->actions()->create(['description' => 'Acción vieja', 'performed_at' => '2026-10-01']);

    Livewire::test(Form::class)
        ->call('editTask', $task->id)
        ->assertSet('concept', 'Old concept')
        ->assertSet('taskActions.0.description', 'Acción vieja')
        ->set('concept', 'New concept')
        ->call('removeAction', 0)
        ->call('addAction')
        ->set('taskActions.0.description', 'Acción nueva')
        ->call('save')
        ->assertHasNoErrors();

    expect($task->refresh()->concept)->toBe('New concept')
        ->and($task->actions()->pluck('description')->all())->toBe(['Acción nueva']);
});
