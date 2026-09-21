<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM task module (routed at /tareas): every task, related to an
 * opportunity or not, soonest end date first. Defaults to the current
 * user's pending tasks. Every authenticated user can create, edit and
 * complete tasks (through the shared App\Livewire\Tasks\Form modal); only
 * admins can delete.
 */
#[Title('Tareas')]
class Index extends Component
{
    use WithPagination;

    /**
     * Assignee filter: "mine" (default), "all", or a user id.
     */
    #[Url(as: 'responsable', history: true)]
    public string $assignee = 'mine';

    /**
     * Status filter: "pending" (default), "completed" or "all".
     */
    #[Url(as: 'estado', history: true)]
    public string $status = 'pending';

    /**
     * Reset to the first page whenever a filter changes.
     */
    public function updatingAssignee(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Re-render the list when the shared form saves a task.
     */
    #[On('task-saved')]
    public function refreshTasks(): void {}

    /**
     * Delete a task. Admins only.
     */
    public function deleteTask(int $taskId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        Task::findOrFail($taskId)->delete();

        Flux::toast(variant: 'success', text: __('Tarea eliminada.'));
    }

    /**
     * Mark a task as completed, or reopen it if it already was.
     */
    public function toggleTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        $task->update(['completed_at' => $task->isCompleted() ? null : now()]);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function render(): View
    {
        return view('livewire.tasks.index', [
            'tasks' => $this->tasks(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Task>
     */
    private function tasks(): LengthAwarePaginator
    {
        return Task::query()
            ->with(['opportunity.organization', 'assignee', 'actions'])
            ->where(
                fn ($query) => $query
                    ->whereNull('opportunity_id')
                    ->orWhereHas('opportunity.organization')
            )
            ->when($this->assignee === 'mine', fn ($query) => $query->where('user_id', auth()->id()))
            ->when(
                ! in_array($this->assignee, ['mine', 'all', ''], true),
                fn ($query) => $query->where('user_id', $this->assignee)
            )
            ->when($this->status === 'pending', fn ($query) => $query->whereNull('completed_at'))
            ->when($this->status === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->orderBy('end_date')
            ->orderBy('id')
            ->paginate(15);
    }
}
