<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Services;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\ReadModels\AbTestManagementVariant;

final readonly class AbTestActivationPolicy
{
    /**
     * @param array<int, AbTestManagementVariant> $variants
     * @return array<int, string>
     */
    public function violations(int $trafficPercent, AbTestDistributionMode $distributionMode, array $variants): array
    {
        $violations = [];

        if ($trafficPercent < 1 || $trafficPercent > 100) {
            $violations[] = 'Active tests must use traffic between 1 and 100 percent.';
        }

        if ($variants === []) {
            $violations[] = 'Active tests must have at least one variant.';

            return $violations;
        }

        if ($distributionMode === AbTestDistributionMode::Equal) {
            return $violations;
        }

        $totalWeight = array_sum(array_map(
            static fn (AbTestManagementVariant $variant): int => $variant->weight,
            $variants,
        ));

        if ($totalWeight <= 0) {
            $violations[] = 'Active tests must have a positive total variant weight.';
        }

        if ($totalWeight !== 100) {
            $violations[] = 'Active tests must have variant weights totaling exactly 100.';
        }

        return $violations;
    }
}
