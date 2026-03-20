<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\ReadModels\PaginatedAbTests;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;

final readonly class GetPaginatedAbTestsAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(AbTestListQuery $query): PaginatedAbTests
    {
        return $this->tests->paginate($query);
    }
}
