<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM follow-up agenda (routed at /tareas): every task across all
 * opportunities, soonest due date first. Defaults to the current user's
 * pending tasks. Tasks are created and edited from the opportunity page.
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
            ->with(['opportunity.organization', 'assignee'])
            ->whereHas('opportunity.organization')
            ->when($this->assignee === 'mine', fn ($query) => $query->where('user_id', auth()->id()))
            ->when(
                ! in_array($this->assignee, ['mine', 'all', ''], true),
                fn ($query) => $query->where('user_id', $this->assignee)
            )
            ->when($this->status === 'pending', fn ($query) => $query->whereNull('completed_at'))
            ->when($this->status === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(15);
    }
}
