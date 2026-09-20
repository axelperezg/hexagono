<?php

namespace App\Models;

use App\Enums\InteractionType;
use Database\Factories\InteractionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * A logged touchpoint (call, meeting, email...) on an opportunity.
 *
 * @property int $id
 * @property int $opportunity_id
 * @property int|null $user_id
 * @property InteractionType $type
 * @property string $subject
 * @property string|null $notes
 * @property Carbon $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['opportunity_id', 'user_id', 'type', 'subject', 'notes', 'occurred_at'])]
class Interaction extends Model
{
    /** @use HasFactory<InteractionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InteractionType::class,
            'occurred_at' => 'datetime',
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
     * The team member who logged the interaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_interaction');
    }
}
