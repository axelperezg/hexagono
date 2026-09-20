<?php

namespace App\Models;

use Database\Factories\OpportunityStageChangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * History entry recording that an opportunity moved to a pipeline stage
 * (from_stage_id is null for its initial stage). Written automatically
 * by App\Models\Opportunity.
 *
 * @property int $id
 * @property int $opportunity_id
 * @property int|null $from_stage_id
 * @property int $to_stage_id
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['opportunity_id', 'from_stage_id', 'to_stage_id', 'user_id'])]
class OpportunityStageChange extends Model
{
    /** @use HasFactory<OpportunityStageChangeFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'from_stage_id');
    }

    /**
     * @return BelongsTo<PipelineStage, $this>
     */
    public function toStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'to_stage_id');
    }

    /**
     * The user who moved the opportunity, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
