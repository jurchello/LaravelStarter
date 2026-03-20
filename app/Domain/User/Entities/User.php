<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use DateTimeImmutable;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isAdmin,
        public bool $isSuperadmin,
        /** @var array<int, string> */
        public array $roles,
        public DateTimeImmutable $registeredAt,
    ) {}
}
