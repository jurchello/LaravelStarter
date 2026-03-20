<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\Contracts;

interface FeatureFlagRuntime
{
    public function registerDefinitions(): void;

    public function purge(string|array|null $keys = null): void;

    public function active(string $key, mixed $scope = null): bool;
}
