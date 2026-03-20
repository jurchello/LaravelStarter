<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class PaginatedAbTests
{
    /**
     * @param array<int, AbTestListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
