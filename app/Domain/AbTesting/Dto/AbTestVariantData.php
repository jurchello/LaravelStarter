<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Dto;

final readonly class AbTestVariantData
{
    public function __construct(
        public string $name,
        public string $slug,
        public int $weight,
    ) {}
}
