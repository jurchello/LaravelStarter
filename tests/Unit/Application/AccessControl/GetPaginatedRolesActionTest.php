<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AccessControl;

use App\Application\AccessControl\GetPaginatedRolesAction;
use App\Domain\AccessControl\ReadModels\PaginatedRoles;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetPaginatedRolesActionTest extends TestCase
{
    private RoleRepository&MockInterface $roles;

    private GetPaginatedRolesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roles = Mockery::mock(RoleRepository::class);
        $this->action = new GetPaginatedRolesAction($this->roles);
    }

    public function test_delegates_to_repository_with_query_object(): void
    {
        $roles = new PaginatedRoles(items: [], total: 0, perPage: 50, currentPage: 1);
        $query = RoleListQuery::fromScalars(page: 2, perPage: 25, search: 'man', sortBy: 'name', direction: 'asc');

        $this->roles->shouldReceive('paginate')
            ->with($query)
            ->once()
            ->andReturn($roles);

        $result = $this->action->execute($query);

        $this->assertSame($roles, $result);
    }
}
