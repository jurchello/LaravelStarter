<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync {--force : Delete stale managed permissions}';

    protected $description = 'Sync managed permissions from named routes.';

    public function handle(): int
    {
        $managedNames = [];
        $unnamedManagedRoutes = [];
        $routes = RouteFacade::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if (! $this->shouldManage($route)) {
                continue;
            }

            $name = $route->getName();

            if (! is_string($name) || $name === '') {
                $unnamedManagedRoutes[] = $route->uri();

                continue;
            }

            $managedNames[] = $name;
        }

        if ($unnamedManagedRoutes !== []) {
            foreach ($unnamedManagedRoutes as $uri) {
                $this->error("Managed route [{$uri}] is missing a name.");
            }

            return self::FAILURE;
        }

        $managedNames = array_values(array_unique($managedNames));
        sort($managedNames);

        $existingManagedNames = Permission::query()
            ->where(static function ($query): void {
                $query
                    ->where('name', 'like', 'admin.%')
                    ->orWhere('name', 'like', 'docs.%');
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $missing = array_values(array_diff($managedNames, $existingManagedNames));
        $stale = array_values(array_diff($existingManagedNames, $managedNames));

        foreach ($missing as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
            $this->line("CREATED {$permissionName}");
        }

        foreach ($stale as $permissionName) {
            $this->warn("STALE {$permissionName}");
        }

        if ($this->option('force')) {
            Permission::query()
                ->whereIn('name', $stale)
                ->get()
                ->each
                ->delete();

            foreach ($stale as $permissionName) {
                $this->line("DELETED {$permissionName}");
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info(sprintf(
            'Permission sync completed. Managed: %d, created: %d, stale: %d.',
            count($managedNames),
            count($missing),
            count($stale),
        ));

        return self::SUCCESS;
    }

    private function shouldManage(Route $route): bool
    {
        $uri = ltrim($route->uri(), '/');
        $name = $route->getName();
        $actionName = ltrim($route->getActionName(), '\\');

        if ($actionName === 'Illuminate\\Routing\\RedirectController') {
            return false;
        }

        if (str_starts_with($uri, 'management')) {
            return true;
        }

        return is_string($name) && str_starts_with($name, 'docs.');
    }
}
