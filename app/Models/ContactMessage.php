<?php

namespace App\Models;

use App\Enums\StudyType;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A message submitted through the public contact form.
 *
 * @property int $id
 * @property string $name
 * @property string|null $institution
 * @property string $email
 * @property string|null $phone
 * @property StudyType $study_type
 * @property string $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'institution', 'email', 'phone', 'study_type', 'message'])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'study_type' => StudyType::class,
        ];
    }
}
