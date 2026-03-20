<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags\Exceptions;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;

final class FeatureFlagNotFound extends ApplicationNotFoundException
{
    public static function forId(int $id): self
    {
        return new self("Feature flag {$id} was not found.");
    }
}
