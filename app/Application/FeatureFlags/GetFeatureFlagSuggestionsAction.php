<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags;

use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;

final readonly class GetFeatureFlagSuggestionsAction
{
    public function __construct(
        private FeatureFlagRepository $flags,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(string $query): array
    {
        return $this->flags->suggestKeys($query);
    }
}
