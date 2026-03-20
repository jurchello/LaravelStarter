<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class GetAbTestManagementViewAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(int $id): AbTestManagementView
    {
        return $this->tests->findManagementView($id) ?? throw AbTestNotFound::forId($id);
    }
}
