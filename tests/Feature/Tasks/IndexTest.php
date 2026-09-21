<?php

use App\Livewire\Tasks\Index;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('the tasks agenda requires authentication', function () {
    $this->get(route('tasks.index'))->assertRedirect(route('login'));
});

test('it shows only the current user\'s pending tasks by default, soonest due first', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($user, 'assignee')->create(['concept' => 'Llamar después', 'end_date' => today()->addDays(5)]);
    Task::factory()->for($user, 'assignee')->create(['concept' => 'Llamar primero', 'end_date' => today()->addDay()]);
    Task::factory()->for($user, 'assignee')->completed()->create(['concept' => 'Tarea terminada']);
    Task::factory()->create(['concept' => 'Tarea ajena']);

    Livewire::test(Index::class)
        ->assertSeeInOrder(['Llamar primero', 'Llamar después'])
        ->assertDontSee('Tarea terminada')
        ->assertDontSee('Tarea ajena');
});

test('it filters tasks by assignee and status', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($other, 'assignee')->create(['concept' => 'Tarea de otro']);
    Task::factory()->for($user, 'assignee')->completed()->create(['concept' => 'Tarea terminada']);

    Livewire::test(Index::class)
        ->set('assignee', (string) $other->id)
        ->assertSee('Tarea de otro')
        ->set('assignee', 'all')
        ->set('status', 'completed')
        ->assertSee('Tarea terminada')
        ->assertDontSee('Tarea de otro')
        ->set('status', 'all')
        ->assertSee('Tarea terminada')
        ->assertSee('Tarea de otro');
});

test('it flags overdue tasks', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($user, 'assignee')->overdue()->create();

    Livewire::test(Index::class)->assertSee('Vencida');
});

test('a user can complete and reopen a task', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $task = Task::factory()->for($user, 'assignee')->create();

    Livewire::test(Index::class)->call('toggleTask', $task->id);

    expect($task->refresh()->isCompleted())->toBeTrue();

    Livewire::test(Index::class)->set('status', 'all')->call('toggleTask', $task->id);

    expect($task->refresh()->isCompleted())->toBeFalse();
});

test('it lists tasks without an opportunity and tasks whose organization was deleted are hidden', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($user, 'assignee')->withoutOpportunity()->create(['concept' => 'Tarea interna']);
    Task::factory()->for($user, 'assignee')
        ->for(Opportunity::factory()->for(Organization::factory()->trashed()), 'opportunity')
        ->create(['concept' => 'Tarea huérfana']);

    Livewire::test(Index::class)
        ->assertSee('Tarea interna')
        ->assertSee('Sin oportunidad')
        ->assertDontSee('Tarea huérfana');
});

test('an admin can delete a task and a non-admin cannot', function () {
    $task = Task::factory()->withoutOpportunity()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Index::class)
        ->call('deleteTask', $task->id)
        ->assertForbidden();

    $this->assertModelExists($task);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Index::class)->call('deleteTask', $task->id);

    $this->assertModelMissing($task);
});
