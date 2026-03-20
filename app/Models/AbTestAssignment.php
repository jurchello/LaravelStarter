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
 * @property int $ab_test_variant_id
 * @property string $visitor_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property AbTestVariant $variant
 * @property \Illuminate\Database\Eloquent\Collection<int, AbTestEvent> $events
 */
class AbTestAssignment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['ab_test_id', 'ab_test_variant_id', 'visitor_id', 'user_id'];

    /** @return BelongsTo<AbTest, $this> */
    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    /** @return BelongsTo<AbTestVariant, $this> */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(AbTestVariant::class, 'ab_test_variant_id');
    }

    /** @return HasMany<AbTestEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AbTestEvent::class);
    }
}
