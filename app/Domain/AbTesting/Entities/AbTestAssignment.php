<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Entities;

final readonly class AbTestAssignment
{
    public function __construct(
        public int $id,
        public int $abTestId,
        public int $abTestVariantId,
        public string $visitorId,
        public ?int $userId,
        public AbTestVariant $variant,
    ) {}
}
