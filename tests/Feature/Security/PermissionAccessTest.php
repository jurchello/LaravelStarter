<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class PermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_with_route_permission_can_access_matching_admin_route(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Permission::create(['name' => 'admin.users.index', 'guard_name' => 'web']);
        $user->givePermissionTo('admin.users.index');

        $this->actingAs($user)
            ->get('/management/users')
            ->assertOk();
    }

    public function test_verified_user_without_route_permission_gets_404_for_admin_route(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Permission::create(['name' => 'admin.users.index', 'guard_name' => 'web']);

        $this->actingAs($user)
            ->get('/management/users')
            ->assertNotFound();
    }

    public function test_superadmin_bypasses_permission_checks(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        Permission::create(['name' => 'admin.users.index', 'guard_name' => 'web']);

        $this->assertTrue($user->can('admin.users.index'));
    }

    public function test_verified_user_with_docs_permission_can_view_api_docs(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Permission::create(['name' => 'docs.site.ui', 'guard_name' => 'web']);
        $user->givePermissionTo('docs.site.ui');

        $this->actingAs($user)
            ->get(route('docs.site.ui'))
            ->assertOk();
    }
}
