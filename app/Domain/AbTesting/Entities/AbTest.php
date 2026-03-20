<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Entities;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;

final readonly class AbTest
{
    /** @param AbTestVariant[] $variants */
    public function __construct(
        public int $id,
        public string $slug,
        public int $trafficPercent,
        public AbTestDistributionMode $distributionMode,
        public array $variants,
    ) {}
}
