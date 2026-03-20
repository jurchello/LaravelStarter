<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class AbTestAnalytics
{
    /**
     * @param  array<string, int>  $eventsByName
     */
    public function __construct(
        public int $assignmentsCount,
        public int $identifiedAssignmentsCount,
        public array $eventsByName,
    ) {}
}
