<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags;

use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;

final readonly class CreateFeatureFlagAction
{
    public function __construct(
        private FeatureFlagRepository $flags,
        private FeatureFlagRuntime $runtime,
    ) {}

    public function execute(FeatureFlagData $data): FeatureFlag
    {
        $flag = $this->flags->create($data);
        $this->runtime->purge($flag->key);

        return $flag;
    }
}
