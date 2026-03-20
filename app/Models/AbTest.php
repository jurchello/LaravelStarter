<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property AbTestStatus $status
 * @property int $traffic_percent
 * @property AbTestDistributionMode|null $distribution_mode
 */
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

    /** @return HasMany<AbTestVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(AbTestVariant::class);
    }

    /** @return HasMany<AbTestAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(AbTestAssignment::class);
    }
}
