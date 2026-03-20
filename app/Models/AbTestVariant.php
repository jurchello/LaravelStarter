<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AbTestAssignment::class);
    }
}