<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Application\AbTesting\Exceptions\AbTestVariantNotFound;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;
use App\Domain\AbTesting\Services\AbTestMutationPolicy;

final readonly class UpdateAbTestVariantAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestActivationPolicy $activationPolicy,
        private AbTestMutationPolicy $mutationPolicy,
    ) {}

    public function execute(int $abTestId, int $variantId, AbTestVariantData $data): AbTestManagementView
    {
        $current = $this->tests->findManagementView($abTestId);

        if ($current === null) {
            throw AbTestNotFound::forId($abTestId);
        }

        $variantFound = false;
        $projectedVariants = array_map(function ($variant) use ($variantId, $data, &$variantFound) {
            if ($variant->id !== $variantId) {
                return $variant;
            }

            $variantFound = true;

            return new \App\Domain\AbTesting\ReadModels\AbTestManagementVariant(
                id: $variant->id,
                name: $data->name,
                slug: $data->slug,
                weight: $data->weight,
                assignmentsCount: $variant->assignmentsCount,
            );
        }, $current->variants);

        if (! $variantFound) {
            throw AbTestVariantNotFound::forId($variantId);
        }

        $currentVariant = current(array_filter(
            $current->variants,
            static fn ($variant): bool => $variant->id === $variantId,
        ));

        $mutationViolations = $this->mutationPolicy->variantUpdateViolations(
            $current->status,
            $currentVariant->slug,
            $data->slug,
        );

        if ($mutationViolations !== []) {
            throw AbTestConfigurationInvalid::fromViolations($mutationViolations);
        }

        if ($current->status === AbTestStatus::Active->value) {
            $violations = $this->activationPolicy->violations($current->trafficPercent, $current->distributionMode, $projectedVariants);

            if ($violations !== []) {
                throw AbTestConfigurationInvalid::fromViolations($violations);
            }
        }

        $view = $this->tests->updateVariant($abTestId, $variantId, $data);

        if ($view === null) {
            throw AbTestVariantNotFound::forId($variantId);
        }

        return $view;
    }
}
