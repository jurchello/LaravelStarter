<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\Dto\AbTestAssignmentDto;
use App\Domain\AbTesting\Entities\AbTestVariant;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\Shared\Randomizer;

final readonly class AssignVariantAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestAssignmentRepository $assignments,
        private Randomizer $randomizer,
    ) {}

    /**
     * Returns the assigned variant slug, or null if the visitor is not enrolled.
     */
    public function execute(string $testSlug, string $visitorId, ?int $userId): ?string
    {
        $test = $this->tests->findActiveBySlug($testSlug);

        if ($test === null) {
            return null;
        }

        if ($userId !== null) {
            $userAssignment = $this->assignments->findByTestAndUser($test->id, $userId);

            if ($userAssignment !== null) {
                return $userAssignment->variant->slug;
            }
        }

        $existing = $this->assignments->findByTestAndVisitor($test->id, $visitorId);

        if ($existing !== null) {
            return $existing->variant->slug;
        }

        if (! $this->isEnrolled($test->trafficPercent)) {
            return null;
        }

        $variant = $this->pickVariant($test->variants, $test->distributionMode);

        if ($variant === null) {
            return null;
        }

        $this->assignments->create(new AbTestAssignmentDto(
            abTestId: $test->id,
            abTestVariantId: $variant->id,
            visitorId: $visitorId,
            userId: $userId,
        ));

        return $variant->slug;
    }

    private function isEnrolled(int $trafficPercent): bool
    {
        return $this->randomizer->int(1, 100) <= $trafficPercent;
    }

    /**
     * @param AbTestVariant[] $variants
     */
    private function pickVariant(array $variants, AbTestDistributionMode $distributionMode): ?AbTestVariant
    {
        if (empty($variants)) {
            return null;
        }

        if ($distributionMode === AbTestDistributionMode::Equal) {
            $index = $this->randomizer->int(0, count($variants) - 1);

            return $variants[$index] ?? null;
        }

        $totalWeight = array_sum(array_map(fn (AbTestVariant $v) => $v->weight, $variants));

        if ($totalWeight <= 0) {
            return null;
        }

        $roll = $this->randomizer->int(1, $totalWeight);
        $cumulative = 0;

        foreach ($variants as $variant) {
            $cumulative += $variant->weight;
            if ($roll <= $cumulative) {
                return $variant;
            }
        }

        return $variants[array_key_last($variants)];
    }
}
