<?php

declare(strict_types=1);

namespace App\Infrastructure\AccessControl\Persistence;

use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Dto\RolePermissionsData;
use App\Domain\AccessControl\Entities\Role as DomainRole;
use App\Domain\AccessControl\ReadModels\PaginatedRoles;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SpatieRoleRepository implements RoleRepository
{
    public function paginate(RoleListQuery $query): PaginatedRoles
    {
        $sortableColumns = [
            'id' => 'id',
            'name' => 'name',
            'usersCount' => 'users_count',
        ];

        $column = $sortableColumns[$query->sortBy] ?? $sortableColumns['name'];
        $direction = $query->direction === 'desc' ? 'desc' : 'asc';

        $paginator = Role::query()
            ->withCount('users')
            ->when($query->search, function ($builder, string $term): void {
                $builder->where('name', 'like', '%'.$term.'%');
            })
            ->orderBy($column, $direction)
            ->when($column !== 'id', fn ($builder) => $builder->orderBy('id'))
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return new PaginatedRoles(
            items: $paginator->getCollection()
                ->map(static fn (Role $role): DomainRole => new DomainRole(
                    id: (int) $role->id,
                    name: $role->name,
                    usersCount: (int) $role->users_count,
                    permissions: $role->permissions()->orderBy('name')->pluck('name')->all(),
                ))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function suggest(string $query, int $limit = 8): array
    {
        $term = trim($query);

        if ($term === '') {
            return [];
        }

        return Role::query()
            ->where('name', 'like', '%'.$term.'%')
            ->orderBy('name')
            ->limit($limit)
            ->pluck('name')
            ->all();
    }

    public function allNames(): array
    {
        return Role::query()->orderBy('name')->pluck('name')->all();
    }

    public function allPermissionNames(): array
    {
        return Permission::query()
            ->where(static function ($query): void {
                $query
                    ->where('name', 'like', 'admin.%')
                    ->orWhere('name', 'like', 'docs.%');
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function create(RoleData $data): DomainRole
    {
        $role = Role::query()->create([
            'name' => $data->name,
            'guard_name' => 'web',
        ]);

        return new DomainRole(
            id: (int) $role->id,
            name: $role->name,
            usersCount: 0,
            permissions: [],
        );
    }

    public function update(int $roleId, RoleData $data): DomainRole
    {
        $role = Role::query()->withCount('users')->findOrFail($roleId);
        $role->forceFill(['name' => $data->name])->save();
        $role->loadCount('users');

        return new DomainRole(
            id: (int) $role->id,
            name: $role->name,
            usersCount: (int) $role->users_count,
            permissions: $role->permissions()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function syncPermissions(int $roleId, RolePermissionsData $data): DomainRole
    {
        $role = Role::query()->withCount('users')->findOrFail($roleId);
        $role->syncPermissions($data->permissions);
        $role->loadCount('users');

        return new DomainRole(
            id: (int) $role->id,
            name: $role->name,
            usersCount: (int) $role->users_count,
            permissions: $role->permissions()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function delete(int $roleId): void
    {
        $role = Role::query()->findOrFail($roleId);
        $role->delete();
    }
}
