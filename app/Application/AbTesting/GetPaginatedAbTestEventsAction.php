<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestEvents;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class GetPaginatedAbTestEventsAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    public function execute(int $id, int $page = 1, int $perPage = 50): PaginatedAbTestEvents
    {
        return $this->tests->paginateEvents($id, $page, $perPage) ?? throw AbTestNotFound::forId($id);
    }
}
