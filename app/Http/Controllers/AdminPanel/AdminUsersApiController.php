<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\User\GetPaginatedUsersAction;
use App\Application\User\UpdateUserRolesAction;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\User\Dto\UserRolesData;
use App\Domain\User\ValueObjects\UserListQuery;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminUserResource;
use App\Http\Resources\Api\PaginationMetaResource;
use App\Models\User;
use App\Support\AdminPanel\UserRoleFilterOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUsersApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetPaginatedUsersAction $getUsers,
        private readonly UpdateUserRolesAction $updateUserRoles,
        private readonly RoleRepository $roles,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = UserListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'registeredAt')->toString(),
            direction: $request->string('direction', 'desc')->toString(),
            search: $request->string('search')->toString(),
            role: $request->string('role', 'all')->toString(),
        );
        $result = $this->getUsers->execute($query);

        return $this->respond(
            data: [
                'items' => AdminUserResource::collection($result->items),
                'roleFilters' => UserRoleFilterOptions::build($this->roles->allNames()),
                'assignableRoles' => $this->roles->allNames(),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => max(1, (int) ceil($result->total / max(1, $result->perPage))),
            ]),
        );
    }

    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $payload = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $updatedUser = $this->updateUserRoles->execute(
            $user->id,
            UserRolesData::fromScalars($payload['roles'] ?? []),
        );

        return $this->respond([
            'user' => new AdminUserResource($updatedUser),
        ]);
    }
}
