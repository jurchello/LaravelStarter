<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use App\Infrastructure\User\Persistence\EloquentUserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentUserRepository();
    }

    public function test_syncs_roles_for_user(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Developer']);

        $result = $this->repository->syncRoles($user->id, ['Manager', 'Developer']);

        $this->assertSame(['Developer', 'Manager'], $result->roles);
        $this->assertSame(['Developer', 'Manager'], $user->fresh()->roles->pluck('name')->sort()->values()->all());
    }
}
