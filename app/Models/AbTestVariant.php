<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $ab_test_id
 * @property string $name
 * @property string $slug
 * @property int $weight
 */
class AbTestVariant extends Model
{
    use HasFactory;

    protected $fillable = ['ab_test_id', 'name', 'slug', 'weight'];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }

    /** @return BelongsTo<AbTest, $this> */
    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    /** @return HasMany<AbTestAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(AbTestAssignment::class);
    }
}
