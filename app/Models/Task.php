<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A piece of work with a start and end date and an assignee, optionally
 * tied to an opportunity, that records the actions carried out on it.
 *
 * @property int $id
 * @property int|null $opportunity_id
 * @property int|null $user_id
 * @property string $concept
 * @property string|null $notes
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['opportunity_id', 'user_id', 'concept', 'start_date', 'end_date', 'notes', 'completed_at'])]
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
            'start_date' => 'date',
            'end_date' => 'date',
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
     * Actions carried out on the task, oldest first.
     *
     * @return HasMany<TaskAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(TaskAction::class)->orderBy('performed_at')->orderBy('id');
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
     * A pending task whose end date is before today.
     */
    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->end_date->isBefore(today());
    }
}
