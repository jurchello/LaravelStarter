<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Entities;

final readonly class AbTestVariant
{
    public function __construct(
        public int $id,
        public string $slug,
        public int $weight,
    ) {}
}
