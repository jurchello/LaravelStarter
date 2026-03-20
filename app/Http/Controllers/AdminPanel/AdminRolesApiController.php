<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AccessControl\CreateRoleAction;
use App\Application\AccessControl\DeleteRoleAction;
use App\Application\AccessControl\GetPaginatedRolesAction;
use App\Application\AccessControl\UpdateRoleAction;
use App\Application\AccessControl\UpdateRolePermissionsAction;
use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Dto\RolePermissionsData;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminRoleResource;
use App\Http\Resources\Api\PaginationMetaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class AdminRolesApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetPaginatedRolesAction $getRoles,
        private readonly CreateRoleAction $createRole,
        private readonly UpdateRoleAction $updateRole,
        private readonly UpdateRolePermissionsAction $updateRolePermissions,
        private readonly DeleteRoleAction $deleteRole,
        private readonly RoleRepository $roles,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = RoleListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
        );
        $result = $this->getRoles->execute($query);

        return $this->respond(
            data: [
                'items' => AdminRoleResource::collection($result->items),
                'availableNames' => $this->roles->allNames(),
                'availablePermissions' => $this->roles->allPermissionNames(),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => (int) ceil($result->total / max(1, $result->perPage)),
            ]),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $role = $this->createRole->execute(RoleData::fromScalars($payload['name']));

        return $this->respond([
            'role' => new AdminRoleResource($role),
        ], status: 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $updatedRole = $this->updateRole->execute((int) $role->id, RoleData::fromScalars($payload['name']));

        return $this->respond([
            'role' => new AdminRoleResource($updatedRole),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->deleteRole->execute((int) $role->id);

        return $this->respond([
            'deleted' => true,
        ]);
    }

    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        $payload = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $updatedRole = $this->updateRolePermissions->execute(
            (int) $role->id,
            RolePermissionsData::fromScalars($payload['permissions'] ?? []),
        );

        return $this->respond([
            'role' => new AdminRoleResource($updatedRole),
        ]);
    }
}
