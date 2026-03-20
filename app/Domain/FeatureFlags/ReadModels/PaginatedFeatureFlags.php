<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\ReadModels;

use App\Domain\FeatureFlags\Entities\FeatureFlag;

final readonly class PaginatedFeatureFlags
{
    /** @param FeatureFlag[] $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
