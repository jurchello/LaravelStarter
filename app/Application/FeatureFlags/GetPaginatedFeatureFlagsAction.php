<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags;

use App\Domain\FeatureFlags\ReadModels\PaginatedFeatureFlags;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;

final readonly class GetPaginatedFeatureFlagsAction
{
    public function __construct(
        private FeatureFlagRepository $flags,
    ) {}

    public function execute(FeatureFlagListQuery $query): PaginatedFeatureFlags
    {
        return $this->flags->paginate($query);
    }
}
