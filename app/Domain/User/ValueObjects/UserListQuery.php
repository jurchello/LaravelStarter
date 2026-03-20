<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final readonly class UserListQuery
{
    private const SORTABLE_FIELDS = [
        'id',
        'name',
        'email',
        'role',
        'registeredAt',
    ];

    public function __construct(
        public int $page = 1,
        public int $perPage = 50,
        public string $sortBy = 'registeredAt',
        public string $direction = 'desc',
        public ?string $search = null,
        public string $role = 'all',
    ) {}

    public static function fromScalars(
        int $page = 1,
        int $perPage = 50,
        string $sortBy = 'registeredAt',
        string $direction = 'desc',
        ?string $search = null,
        string $role = 'all',
    ): self {
        return new self(
            page: max(1, $page),
            perPage: max(1, $perPage),
            sortBy: in_array($sortBy, self::SORTABLE_FIELDS, true) ? $sortBy : 'registeredAt',
            direction: $direction === 'asc' ? 'asc' : 'desc',
            search: self::normalizeSearch($search),
            role: self::normalizeRole($role),
        );
    }

    private static function normalizeSearch(?string $search): ?string
    {
        $normalized = trim((string) $search);

        return $normalized !== '' ? $normalized : null;
    }

    private static function normalizeRole(string $role): string
    {
        $normalized = trim($role);

        return $normalized !== '' ? $normalized : 'all';
    }
}
