<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbTestAssignment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['ab_test_id', 'ab_test_variant_id', 'visitor_id', 'user_id'];

    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(AbTestVariant::class, 'ab_test_variant_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AbTestEvent::class);
    }
}
