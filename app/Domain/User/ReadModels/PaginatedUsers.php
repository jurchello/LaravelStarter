<?php

declare(strict_types=1);

namespace App\Domain\User\ReadModels;

use App\Domain\User\Entities\User;

final readonly class PaginatedUsers
{
    /** @param User[] $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
