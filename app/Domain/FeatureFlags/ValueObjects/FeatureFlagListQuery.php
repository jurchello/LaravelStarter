<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\ValueObjects;

final readonly class FeatureFlagListQuery
{
    private const SORTABLE_FIELDS = [
        'id',
        'key',
        'name',
        'enabled',
        'rolloutPercent',
    ];

    public function __construct(
        public int $page = 1,
        public int $perPage = 50,
        public string $sortBy = 'name',
        public string $direction = 'asc',
        public ?string $search = null,
        public string $status = 'all',
    ) {}

    public static function fromScalars(
        int $page = 1,
        int $perPage = 50,
        string $sortBy = 'name',
        string $direction = 'asc',
        ?string $search = null,
        string $status = 'all',
    ): self {
        return new self(
            page: max(1, $page),
            perPage: max(1, $perPage),
            sortBy: in_array($sortBy, self::SORTABLE_FIELDS, true) ? $sortBy : 'name',
            direction: $direction === 'desc' ? 'desc' : 'asc',
            search: self::normalizeSearch($search),
            status: in_array($status, ['all', 'enabled', 'disabled'], true) ? $status : 'all',
        );
    }

    private static function normalizeSearch(?string $search): ?string
    {
        $normalized = trim((string) $search);

        return $normalized !== '' ? $normalized : null;
    }
}
