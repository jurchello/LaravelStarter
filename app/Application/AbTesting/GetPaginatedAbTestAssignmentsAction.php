<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestAssignments;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class GetPaginatedAbTestAssignmentsAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(int $id, int $page = 1, int $perPage = 50): PaginatedAbTestAssignments
    {
        return $this->tests->paginateAssignments($id, $page, $perPage) ?? throw AbTestNotFound::forId($id);
    }
}
