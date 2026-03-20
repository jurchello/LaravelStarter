<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class PaginatedAbTestEvents
{
    /**
     * @param array<int, AbTestEventListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
