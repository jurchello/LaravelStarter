<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\Entities;

final readonly class Role
{
    public function __construct(
        public int $id,
        public string $name,
        public int $usersCount,
        /** @var array<int, string> */
        public array $permissions = [],
    ) {}
}
