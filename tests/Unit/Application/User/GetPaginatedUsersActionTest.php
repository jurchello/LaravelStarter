<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User;

use App\Application\User\GetPaginatedUsersAction;
use App\Domain\User\ReadModels\PaginatedUsers;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\UserListQuery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetPaginatedUsersActionTest extends TestCase
{
    private UserRepository&MockInterface $users;

    private GetPaginatedUsersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepository::class);
        $this->action = new GetPaginatedUsersAction($this->users);
    }

    public function test_delegates_to_repository_with_query_object(): void
    {
        $users = new PaginatedUsers(items: [], total: 0, perPage: 50, currentPage: 1);
        $query = UserListQuery::fromScalars(
            page: 2,
            perPage: 25,
            sortBy: 'name',
            direction: 'asc',
            search: 'alice',
            role: 'admin',
        );

        $this->users->shouldReceive('paginate')
            ->with($query)
            ->once()
            ->andReturn($users);

        $result = $this->action->execute($query);

        $this->assertSame($users, $result);
    }
}
