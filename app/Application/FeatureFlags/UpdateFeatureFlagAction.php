<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags;

use App\Application\FeatureFlags\Exceptions\FeatureFlagNotFound;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class UpdateFeatureFlagAction
{
    public function __construct(
        private FeatureFlagRepository $flags,
        private FeatureFlagRuntime $runtime,
    ) {}

    public function execute(int $id, FeatureFlagData $data): FeatureFlag
    {
        $existing = $this->flags->findById($id);

        if ($existing === null) {
            throw FeatureFlagNotFound::forId($id);
        }

        try {
            $flag = $this->flags->update($id, $data);
        } catch (ModelNotFoundException) {
            throw FeatureFlagNotFound::forId($id);
        }

        $keys = array_values(array_unique(array_filter([
            $existing->key,
            $flag->key,
        ])));

        $this->runtime->purge($keys);

        return $flag;
    }
}
