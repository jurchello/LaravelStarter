<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Dto;

final readonly class AbTestAssignmentDto
{
    public function __construct(
        public int $abTestId,
        public int $abTestVariantId,
        public string $visitorId,
        public ?int $userId,
    ) {}
}
