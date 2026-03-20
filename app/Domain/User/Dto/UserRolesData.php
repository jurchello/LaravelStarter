<?php

declare(strict_types=1);

namespace App\Domain\User\Dto;

final readonly class UserRolesData
{
    /** @param array<int, string> $roles */
    public function __construct(
        public array $roles,
    ) {}

    /**
     * @param  array<int, string>  $roles
     */
    public static function fromScalars(array $roles): self
    {
        $normalized = array_values(array_unique(array_filter(
            array_map(static fn (mixed $role): string => trim((string) $role), $roles),
            static fn (string $role): bool => $role !== '',
        )));
        sort($normalized);

        return new self($normalized);
    }
}
