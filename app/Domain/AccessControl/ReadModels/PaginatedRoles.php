<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\ReadModels;

use App\Domain\AccessControl\Entities\Role;

final readonly class PaginatedRoles
{
    /** @param Role[] $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
