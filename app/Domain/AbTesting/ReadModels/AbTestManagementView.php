<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;

final readonly class AbTestManagementView
{
    /**
     * @param array<int, AbTestManagementVariant> $variants
     * @param array<int, AbTestRecentAssignment> $recentAssignments
     * @param array<int, AbTestRecentEvent> $recentEvents
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $status,
        public int $trafficPercent,
        public AbTestDistributionMode $distributionMode,
        public array $variants,
        public AbTestAnalytics $analytics,
        public array $recentAssignments,
        public array $recentEvents,
    ) {}
}
