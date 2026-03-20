<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\Dto;

final readonly class FeatureFlagData
{
    public function __construct(
        public string $key,
        public string $name,
        public ?string $description,
        public bool $enabled,
        public int $rolloutPercent,
    ) {}
}
