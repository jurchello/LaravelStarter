<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\Repositories;

use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\ReadModels\PaginatedFeatureFlags;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;

interface FeatureFlagRepository
{
    public function paginate(FeatureFlagListQuery $query): PaginatedFeatureFlags;

    /**
     * @return array<int, string>
     */
    public function suggestKeys(string $query, int $limit = 8): array;

    public function findById(int $id): ?FeatureFlag;

    public function findByKey(string $key): ?FeatureFlag;

    public function create(FeatureFlagData $data): FeatureFlag;

    public function update(int $id, FeatureFlagData $data): FeatureFlag;

    public function delete(int $id): void;
}
