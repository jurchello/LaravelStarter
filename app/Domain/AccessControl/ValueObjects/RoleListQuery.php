<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\ValueObjects;

final readonly class RoleListQuery
{
    private const SORTABLE_FIELDS = [
        'id',
        'name',
        'usersCount',
    ];

    public function __construct(
        public int $page = 1,
        public int $perPage = 50,
        public string $sortBy = 'name',
        public string $direction = 'asc',
        public ?string $search = null,
    ) {}

    public static function fromScalars(
        int $page = 1,
        int $perPage = 50,
        string $sortBy = 'name',
        string $direction = 'asc',
        ?string $search = null,
    ): self {
        return new self(
            page: max(1, $page),
            perPage: max(1, $perPage),
            sortBy: in_array($sortBy, self::SORTABLE_FIELDS, true) ? $sortBy : 'name',
            direction: $direction === 'desc' ? 'desc' : 'asc',
            search: self::normalizeSearch($search),
        );
    }

    private static function normalizeSearch(?string $search): ?string
    {
        $normalized = trim((string) $search);

        return $normalized !== '' ? $normalized : null;
    }
}
