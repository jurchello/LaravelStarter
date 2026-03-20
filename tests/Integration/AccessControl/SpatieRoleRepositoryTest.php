<?php

declare(strict_types=1);

namespace Tests\Integration\AccessControl;

use App\Domain\AccessControl\ReadModels\PaginatedRoles;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;
use App\Infrastructure\AccessControl\Persistence\SpatieRoleRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SpatieRoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SpatieRoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new SpatieRoleRepository();
    }

    public function test_paginates_roles(): void
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);

        $result = $this->repository->paginate(RoleListQuery::fromScalars(page: 1, perPage: 50));

        $this->assertInstanceOf(PaginatedRoles::class, $result);
        $this->assertSame(2, $result->total);
    }

    public function test_includes_users_count(): void
    {
        $role = Role::create(['name' => 'Developer']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $result = $this->repository->paginate(RoleListQuery::fromScalars(page: 1, perPage: 50, search: 'dev'));

        $this->assertSame('Developer', $result->items[0]->name);
        $this->assertSame(1, $result->items[0]->usersCount);
    }
}
