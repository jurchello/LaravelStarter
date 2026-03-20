<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;
use App\Domain\AbTesting\Services\AbTestMutationPolicy;

final readonly class UpdateAbTestAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestActivationPolicy $activationPolicy,
        private AbTestMutationPolicy $mutationPolicy,
    ) {}

    public function execute(int $id, AbTestData $data): AbTestManagementView
    {
        $current = $this->tests->findManagementView($id);

        if ($current === null) {
            throw AbTestNotFound::forId($id);
        }

        $mutationViolations = $this->mutationPolicy->testUpdateViolations($current->status, $current->slug, $data->slug);

        if ($mutationViolations !== []) {
            throw AbTestConfigurationInvalid::fromViolations($mutationViolations);
        }

        if ($current->status === AbTestStatus::Active->value) {
            $violations = $this->activationPolicy->violations($data->trafficPercent, $data->distributionMode, $current->variants);

            if ($violations !== []) {
                throw AbTestConfigurationInvalid::fromViolations($violations);
            }
        }

        return $this->tests->updateManagementView($id, $data) ?? throw AbTestNotFound::forId($id);
    }
}
