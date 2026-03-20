<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Services;

use App\Domain\AbTesting\Enums\AbTestStatus;

final readonly class AbTestMutationPolicy
{
    /**
     * @return array<int, string>
     */
    public function testUpdateViolations(string $currentStatus, string $currentSlug, string $nextSlug): array
    {
        $violations = [];

        if ($currentSlug !== $nextSlug) {
            $violations[] = 'Test slug cannot change after creation.';
        }

        return $violations;
    }

    /**
     * @return array<int, string>
     */
    public function variantUpdateViolations(string $currentStatus, string $currentSlug, string $nextSlug): array
    {
        $violations = [];

        if ($currentStatus !== AbTestStatus::Draft->value && $currentSlug !== $nextSlug) {
            $violations[] = 'Variant slug cannot change after the draft state.';
        }

        return $violations;
    }

    /**
     * @return array<int, string>
     */
    public function statusTransitionViolations(string $currentStatus, AbTestStatus $nextStatus): array
    {
        return match ($currentStatus) {
            AbTestStatus::Draft->value => $nextStatus === AbTestStatus::Active ? [] : ['Draft tests can only move to active.'],
            AbTestStatus::Active->value => in_array($nextStatus, [AbTestStatus::Paused, AbTestStatus::Finished], true) ? [] : ['Active tests can only be paused or finished.'],
            AbTestStatus::Paused->value => in_array($nextStatus, [AbTestStatus::Active, AbTestStatus::Finished], true) ? [] : ['Paused tests can only return to active or move to finished.'],
            AbTestStatus::Finished->value => ['Finished tests cannot change status.'],
            default => ['Unsupported AB test status transition.'],
        };
    }
}
