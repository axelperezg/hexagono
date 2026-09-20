<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A free-form label shared by organizations and opportunities.
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * Resolve the tag ids for a form: the already existing tags that were
     * ticked plus any new comma-separated names, which are created when
     * no tag with that name exists yet.
     *
     * @param  array<int, int|string>  $existingIds
     * @return array<int, int>
     */
    public static function resolveIds(array $existingIds, string $newNames): array
    {
        $created = collect(explode(',', $newNames))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => mb_strtolower($name))
            ->map(fn (string $name) => static::query()->whereRaw('lower(name) = ?', [mb_strtolower($name)])->first()
                ?? static::create(['name' => $name]))
            ->pluck('id');

        return collect($existingIds)->map(fn ($id) => (int) $id)->merge($created)->unique()->values()->all();
    }
}
