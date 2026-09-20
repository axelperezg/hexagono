<?php

namespace App\Models;

use Database\Factories\PipelineStageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A configurable step of the sales pipeline (see PipelineStageSeeder).
 *
 * @property int $id
 * @property string $name
 * @property int $position
 * @property bool $is_won
 * @property bool $is_lost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'position', 'is_won', 'is_lost'])]
class PipelineStage extends Model
{
    /** @use HasFactory<PipelineStageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Opportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
