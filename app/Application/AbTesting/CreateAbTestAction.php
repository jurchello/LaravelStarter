<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class CreateAbTestAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(AbTestData $data): AbTestManagementView
    {
        return $this->tests->createManagementView($data);
    }
}
