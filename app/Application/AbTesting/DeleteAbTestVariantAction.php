<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Application\AbTesting\Exceptions\AbTestVariantNotFound;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;

final readonly class DeleteAbTestVariantAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestActivationPolicy $activationPolicy,
    ) {}

    public function execute(int $abTestId, int $variantId): AbTestManagementView
    {
        $current = $this->tests->findManagementView($abTestId);

        if ($current === null) {
            throw AbTestNotFound::forId($abTestId);
        }

        $projectedVariants = array_values(array_filter(
            $current->variants,
            static fn ($variant): bool => $variant->id !== $variantId,
        ));

        if (count($projectedVariants) === count($current->variants)) {
            throw AbTestVariantNotFound::forId($variantId);
        }

        if ($current->status === AbTestStatus::Active->value) {
            $violations = $this->activationPolicy->violations($current->trafficPercent, $current->distributionMode, $projectedVariants);

            if ($violations !== []) {
                throw AbTestConfigurationInvalid::fromViolations($violations);
            }
        }

        $view = $this->tests->deleteVariant($abTestId, $variantId);

        if ($view === null) {
            throw AbTestVariantNotFound::forId($variantId);
        }

        return $view;
    }
}
