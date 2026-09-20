<?php

namespace App\Models;

use App\Concerns\HasTags;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * An approach to an organization with business potential.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $pipeline_stage_id
 * @property int|null $user_id
 * @property string $title
 * @property string|null $estimated_amount
 * @property string $currency
 * @property Carbon|null $expected_close_date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['organization_id', 'pipeline_stage_id', 'user_id', 'title', 'estimated_amount', 'currency', 'expected_close_date', 'notes'])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory, HasTags, SoftDeletes;

    /**
     * Record the pipeline stage history: the initial stage on creation and
     * every later stage change, attributed to the authenticated user.
     */
    protected static function booted(): void
    {
        static::created(fn (Opportunity $opportunity) => $opportunity->recordStageChange(null));

        static::updated(function (Opportunity $opportunity) {
            if ($opportunity->wasChanged('pipeline_stage_id')) {
                $opportunity->recordStageChange((int) $opportunity->getOriginal('pipeline_stage_id'));
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
            'expected_close_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    /**
     * The team member responsible for the opportunity.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Interaction, $this>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany<OpportunityStageChange, $this>
     */
    public function stageChanges(): HasMany
    {
        return $this->hasMany(OpportunityStageChange::class);
    }

    private function recordStageChange(?int $fromStageId): void
    {
        $this->stageChanges()->create([
            'from_stage_id' => $fromStageId,
            'to_stage_id' => $this->pipeline_stage_id,
            'user_id' => Auth::id(),
        ]);
    }
}
