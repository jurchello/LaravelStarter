<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ReadModels\AbTestManagementVariant;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;

final readonly class CreateAbTestVariantAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestActivationPolicy $activationPolicy,
    ) {}

    public function execute(int $abTestId, AbTestVariantData $data): AbTestManagementView
    {
        $current = $this->tests->findManagementView($abTestId);

        if ($current === null) {
            throw AbTestNotFound::forId($abTestId);
        }

        if ($current->status === AbTestStatus::Active->value) {
            $violations = $this->activationPolicy->violations(
                $current->trafficPercent,
                $current->distributionMode,
                [
                    ...$current->variants,
                    new AbTestManagementVariant(
                        id: 0,
                        name: $data->name,
                        slug: $data->slug,
                        weight: $data->weight,
                        assignmentsCount: 0,
                    ),
                ],
            );

            if ($violations !== []) {
                throw AbTestConfigurationInvalid::fromViolations($violations);
            }
        }

        return $this->tests->createVariant($abTestId, $data) ?? throw AbTestNotFound::forId($abTestId);
    }
}
