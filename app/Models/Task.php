<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A pending follow-up on an opportunity, with a due date and an assignee.
 *
 * @property int $id
 * @property int $opportunity_id
 * @property int|null $user_id
 * @property string $title
 * @property string|null $notes
 * @property Carbon $due_date
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['opportunity_id', 'user_id', 'title', 'notes', 'due_date', 'completed_at'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * The team member responsible for the task.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder<Task>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * A pending task whose due date is before today.
     */
    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->due_date->isBefore(today());
    }
}
