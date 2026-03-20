<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class AbTestListItem
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $status,
        public int $trafficPercent,
        public int $variantsCount,
    ) {}
}
