<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AccessControl;

use App\Application\AccessControl\DeleteRoleAction;
use App\Application\AccessControl\Exceptions\RoleNotFound;
use App\Application\AccessControl\UpdateRoleAction;
use App\Application\AccessControl\UpdateRolePermissionsAction;
use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Dto\RolePermissionsData;
use App\Domain\AccessControl\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class RoleMutationActionsTest extends TestCase
{
    private RoleRepository&MockInterface $roles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roles = Mockery::mock(RoleRepository::class);
    }

    public function test_update_role_rethrows_repository_model_not_found_as_role_not_found(): void
    {
        $action = new UpdateRoleAction($this->roles);
        $data = RoleData::fromScalars('Manager');

        $this->roles->shouldReceive('update')
            ->with(5, $data)
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->expectException(RoleNotFound::class);

        $action->execute(5, $data);
    }

    public function test_delete_role_rethrows_repository_model_not_found_as_role_not_found(): void
    {
        $action = new DeleteRoleAction($this->roles);

        $this->roles->shouldReceive('delete')
            ->with(5)
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->expectException(RoleNotFound::class);

        $action->execute(5);
    }

    public function test_update_role_permissions_rethrows_repository_model_not_found_as_role_not_found(): void
    {
        $action = new UpdateRolePermissionsAction($this->roles);
        $data = RolePermissionsData::fromScalars(['admin.dashboard']);

        $this->roles->shouldReceive('syncPermissions')
            ->with(5, $data)
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->expectException(RoleNotFound::class);

        $action->execute(5, $data);
    }
}
