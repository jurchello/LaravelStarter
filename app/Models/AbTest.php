<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbTest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'traffic_percent', 'distribution_mode'];

    protected function casts(): array
    {
        return [
            'status' => AbTestStatus::class,
            'traffic_percent' => 'integer',
            'distribution_mode' => AbTestDistributionMode::class,
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(AbTestVariant::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AbTestAssignment::class);
    }
}
