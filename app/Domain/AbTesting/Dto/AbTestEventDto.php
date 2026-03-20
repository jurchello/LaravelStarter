<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Dto;

final readonly class AbTestEventDto
{
    public function __construct(
        public int $abTestAssignmentId,
        public string $event,
    ) {}
}
