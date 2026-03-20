<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class DeleteAbTestAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(int $id): void
    {
        if (! $this->tests->deleteManagementView($id)) {
            throw AbTestNotFound::forId($id);
        }
    }
}
