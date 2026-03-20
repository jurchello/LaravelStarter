<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlags\Entities;

final readonly class FeatureFlag
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
        public ?string $description,
        public bool $enabled,
        public int $rolloutPercent,
    ) {}
}
