<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\Repositories;

use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Dto\RolePermissionsData;
use App\Domain\AccessControl\Entities\Role;
use App\Domain\AccessControl\ReadModels\PaginatedRoles;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;

interface RoleRepository
{
    public function paginate(RoleListQuery $query): PaginatedRoles;

    /**
     * @return array<int, string>
     */
    public function suggest(string $query, int $limit = 8): array;

    /**
     * @return array<int, string>
     */
    public function allNames(): array;

    /**
     * @return array<int, string>
     */
    public function allPermissionNames(): array;

    public function create(RoleData $data): Role;

    public function update(int $roleId, RoleData $data): Role;

    public function syncPermissions(int $roleId, RolePermissionsData $data): Role;

    public function delete(int $roleId): void;
}
