<?php

declare(strict_types=1);

namespace Tests\Unit\Application\FeatureFlags;

use App\Application\FeatureFlags\GetPaginatedFeatureFlagsAction;
use App\Domain\FeatureFlags\ReadModels\PaginatedFeatureFlags;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetPaginatedFeatureFlagsActionTest extends TestCase
{
    private FeatureFlagRepository&MockInterface $flags;

    private GetPaginatedFeatureFlagsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flags = Mockery::mock(FeatureFlagRepository::class);
        $this->action = new GetPaginatedFeatureFlagsAction($this->flags);
    }

    public function test_delegates_to_repository_with_query_object(): void
    {
        $query = FeatureFlagListQuery::fromScalars(page: 2, perPage: 25, search: 'beta', sortBy: 'key', direction: 'asc', status: 'enabled');
        $result = new PaginatedFeatureFlags(items: [], total: 0, perPage: 25, currentPage: 2);

        $this->flags->shouldReceive('paginate')
            ->once()
            ->with($query)
            ->andReturn($result);

        self::assertSame($result, $this->action->execute($query));
    }
}
