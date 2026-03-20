<?php

declare(strict_types=1);

namespace App\Infrastructure\FeatureFlags\Pennant;

use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Lottery;
use Laravel\Pennant\Feature;

final class PennantFeatureRuntime implements FeatureFlagRuntime
{
    public function registerDefinitions(): void
    {
        if (! Schema::hasTable('feature_flags')) {
            return;
        }

        foreach (FeatureFlag::query()->get(['key', 'enabled', 'rollout_percent']) as $flag) {
            Feature::define($flag->key, function () use ($flag): bool|Lottery {
                if (! $flag->enabled) {
                    return false;
                }

                if ($flag->rollout_percent >= 100) {
                    return true;
                }

                if ($flag->rollout_percent <= 0) {
                    return false;
                }

                return Lottery::odds($flag->rollout_percent, 100);
            });
        }
    }

    public function purge(string|array|null $keys = null): void
    {
        Feature::purge($keys);
        Feature::forgetDrivers();
        $this->registerDefinitions();
    }

    public function active(string $key, mixed $scope = null): bool
    {
        if ($scope === null) {
            return Feature::active($key);
        }

        return Feature::for($scope)->active($key);
    }
}
