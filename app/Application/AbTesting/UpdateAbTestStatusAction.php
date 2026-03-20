<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;
use App\Domain\AbTesting\Services\AbTestMutationPolicy;

final readonly class UpdateAbTestStatusAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestActivationPolicy $activationPolicy,
        private AbTestMutationPolicy $mutationPolicy,
    ) {}

    public function execute(int $id, AbTestStatus $status): AbTestManagementView
    {
        $current = $this->tests->findManagementView($id);

        if ($current === null) {
            throw AbTestNotFound::forId($id);
        }

        $transitionViolations = $this->mutationPolicy->statusTransitionViolations($current->status, $status);

        if ($transitionViolations !== []) {
            throw AbTestConfigurationInvalid::fromViolations($transitionViolations);
        }

        if ($status === AbTestStatus::Active) {
            $violations = $this->activationPolicy->violations($current->trafficPercent, $current->distributionMode, $current->variants);

            if ($violations !== []) {
                throw AbTestConfigurationInvalid::fromViolations($violations);
            }
        }

        return $this->tests->updateStatus($id, $status) ?? throw AbTestNotFound::forId($id);
    }
}
