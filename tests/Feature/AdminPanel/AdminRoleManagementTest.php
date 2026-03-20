<?php

declare(strict_types=1);

namespace Tests\Feature\AdminPanel;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\DisablesCsrfForWebMutations;
use Tests\TestCase;

final class AdminRoleManagementTest extends TestCase
{
    use DisablesCsrfForWebMutations;
    use RefreshDatabase;

    public function test_admin_roles_page_renders_server_side_initial_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $role = Role::create(['name' => 'Manager']);

        $response = $this->actingAs($admin)->get('/management/roles');

        $response->assertOk()
            ->assertViewIs('admin-panel.roles.index')
            ->assertSee('data-admin-page="roles"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-roles-endpoint="/management/api/roles"', false)
            ->assertSee('data-roles-suggestions-endpoint="/management/api/roles/suggestions"', false)
            ->assertSee('data-roles-permission-update-base=', false)
            ->assertSee('roles-table-row', false)
            ->assertSee($role->name);
    }

    public function test_admin_roles_api_returns_paginated_envelope(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);

        $response = $this->actingAs($admin)->getJson('/management/api/roles');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'name', 'usersCount', 'permissions'],
                    ],
                    'availableNames',
                    'availablePermissions',
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ]);
    }

    public function test_admin_can_filter_roles_by_search(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);

        $response = $this->actingAs($admin)->getJson('/management/api/roles?search=man');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Manager');
    }

    public function test_admin_can_create_update_and_delete_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $created = $this->actingAs($admin)->postJson('/management/api/roles', [
            'name' => 'Developer',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.role.name', 'Developer');

        $roleId = $created->json('data.role.id');

        $updated = $this->actingAs($admin)->putJson("/management/api/roles/{$roleId}", [
            'name' => 'Engineer',
        ]);

        $updated->assertOk()
            ->assertJsonPath('data.role.name', 'Engineer');

        $deleted = $this->actingAs($admin)->deleteJson("/management/api/roles/{$roleId}");

        $deleted->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing(config('permission.table_names.roles'), [
            'id' => $roleId,
        ]);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $role = Role::create(['name' => 'Manager']);
        Permission::create(['name' => 'admin.users.index', 'guard_name' => 'web']);
        Permission::create(['name' => 'admin.roles.index', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->patchJson("/management/api/roles/{$role->id}/permissions", [
            'permissions' => ['admin.users.index', 'admin.roles.index'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role.permissions.0', 'admin.roles.index')
            ->assertJsonPath('data.role.permissions.1', 'admin.users.index');

        $this->assertTrue($role->fresh()->hasPermissionTo('admin.users.index'));
        $this->assertTrue($role->fresh()->hasPermissionTo('admin.roles.index'));
    }

    public function test_admin_role_suggestions_api_returns_matching_names(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Role::create(['name' => 'Developer']);

        $response = $this->actingAs($admin)->getJson('/management/api/roles/suggestions?query=dev');

        $response->assertOk()
            ->assertJsonPath('data.items.0', 'Developer');
    }
}
