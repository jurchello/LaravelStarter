<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Dto;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;

final readonly class AbTestData
{
    public function __construct(
        public string $name,
        public string $slug,
        public int $trafficPercent,
        public AbTestDistributionMode $distributionMode,
    ) {}
}
