<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\Dto;

final readonly class RolePermissionsData
{
    /** @param array<int, string> $permissions */
    public function __construct(
        public array $permissions,
    ) {}

    /**
     * @param  array<int, string>  $permissions
     */
    public static function fromScalars(array $permissions): self
    {
        $normalized = array_values(array_unique(array_filter(
            array_map(static fn (mixed $permission): string => trim((string) $permission), $permissions),
            static fn (string $permission): bool => $permission !== '',
        )));
        sort($normalized);

        return new self($normalized);
    }
}
