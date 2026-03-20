<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\GetPaginatedAbTestsAction;
use App\Domain\AbTesting\ReadModels\PaginatedAbTests;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetPaginatedAbTestsActionTest extends TestCase
{
    private AbTestRepository&MockInterface $tests;

    private GetPaginatedAbTestsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = Mockery::mock(AbTestRepository::class);
        $this->action = new GetPaginatedAbTestsAction($this->tests);
    }

    public function test_delegates_to_repository_with_query_object(): void
    {
        $query = AbTestListQuery::fromScalars(
            page: 2,
            perPage: 25,
            sortBy: 'variantsCount',
            direction: 'desc',
            search: 'home',
            status: 'active',
        );
        $result = new PaginatedAbTests(items: [], total: 0, perPage: 25, currentPage: 2);

        $this->tests->shouldReceive('paginate')
            ->with($query)
            ->once()
            ->andReturn($result);

        $this->assertSame($result, $this->action->execute($query));
    }
}
