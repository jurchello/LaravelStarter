<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User;

use App\Application\User\Exceptions\UserNotFound;
use App\Application\User\UpdateUserRolesAction;
use App\Domain\User\Dto\UserRolesData;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepository;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateUserRolesActionTest extends TestCase
{
    private UserRepository&MockInterface $users;
    private UpdateUserRolesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepository::class);
        $this->action = new UpdateUserRolesAction($this->users);
    }

    public function test_delegates_role_sync_to_repository(): void
    {
        $data = UserRolesData::fromScalars(['Manager', 'Developer']);
        $user = new User(
            id: 8,
            name: 'Alice',
            email: 'alice@example.com',
            isAdmin: false,
            isSuperadmin: false,
            roles: ['Developer', 'Manager'],
            registeredAt: new DateTimeImmutable('2026-03-19T00:00:00+00:00'),
        );

        $this->users->shouldReceive('syncRoles')
            ->with(8, ['Developer', 'Manager'])
            ->once()
            ->andReturn($user);

        $result = $this->action->execute(8, $data);

        $this->assertSame($user, $result);
    }

    public function test_rethrows_repository_model_not_found_as_user_not_found(): void
    {
        $data = UserRolesData::fromScalars(['Manager']);

        $this->users->shouldReceive('syncRoles')
            ->with(8, ['Manager'])
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->expectException(UserNotFound::class);

        $this->action->execute(8, $data);
    }
}
