<?php

use App\Livewire\Tasks\Index;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('the tasks agenda requires authentication', function () {
    $this->get(route('tasks.index'))->assertRedirect(route('login'));
});

test('it shows only the current user\'s pending tasks by default, soonest due first', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($user, 'assignee')->create(['title' => 'Llamar después', 'due_date' => today()->addDays(5)]);
    Task::factory()->for($user, 'assignee')->create(['title' => 'Llamar primero', 'due_date' => today()->addDay()]);
    Task::factory()->for($user, 'assignee')->completed()->create(['title' => 'Tarea terminada']);
    Task::factory()->create(['title' => 'Tarea ajena']);

    Livewire::test(Index::class)
        ->assertSeeInOrder(['Llamar primero', 'Llamar después'])
        ->assertDontSee('Tarea terminada')
        ->assertDontSee('Tarea ajena');
});

test('it filters tasks by assignee and status', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user);

    Task::factory()->for($other, 'assignee')->create(['title' => 'Tarea de otro']);
    Task::factory()->for($user, 'assignee')->completed()->create(['title' => 'Tarea terminada']);

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
