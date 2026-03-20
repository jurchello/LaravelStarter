<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SyncPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_managed_permissions_from_named_routes_without_assigning_them_to_roles(): void
    {
        Role::create(['name' => 'Admin']);

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('Permission sync completed.')
            ->assertSuccessful();

        $this->assertDatabaseHas('permissions', ['name' => 'admin.dashboard']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.api.users.index']);
        $this->assertFalse(Role::findByName('Admin')->hasPermissionTo('admin.dashboard'));
    }

    public function test_command_reports_stale_permissions_without_deleting_them_by_default(): void
    {
        Permission::create(['name' => 'admin.legacy', 'guard_name' => 'web']);

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('STALE admin.legacy')
            ->assertSuccessful();

        $this->assertDatabaseHas('permissions', ['name' => 'admin.legacy']);
    }

    public function test_command_deletes_stale_permissions_with_force(): void
    {
        Permission::create(['name' => 'admin.legacy', 'guard_name' => 'web']);

        $this->artisan('permissions:sync --force')
            ->expectsOutputToContain('DELETED admin.legacy')
            ->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'admin.legacy']);
    }

    public function test_command_fails_when_managed_route_has_no_name(): void
    {
        Route::middleware('web')->prefix('management')->group(function (): void {
            Route::get('/unnamed-sync-permission-check', static fn () => response()->noContent());
        });

        $this->artisan('permissions:sync')
            ->expectsOutputToContain('Managed route [management/unnamed-sync-permission-check] is missing a name.')
            ->assertFailed();
    }
}
