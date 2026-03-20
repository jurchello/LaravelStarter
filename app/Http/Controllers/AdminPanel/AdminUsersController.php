<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\User\GetPaginatedUsersAction;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\User\ValueObjects\UserListQuery;
use App\Http\Controllers\Controller;
use App\Support\AdminPanel\UserRoleFilterOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminUsersController extends Controller
{
    public function __construct(
        private readonly GetPaginatedUsersAction $getUsers,
        private readonly RoleRepository $roles,
    ) {}

    public function index(Request $request): View
    {
        $query = UserListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'registeredAt')->toString(),
            direction: $request->string('direction', 'desc')->toString(),
            search: $request->string('search')->toString(),
            role: $request->string('role', 'all')->toString(),
        );
        $users = $this->getUsers->execute($query);

        return view('admin-panel.users.index', [
            'users' => $users,
            'query' => $query,
            'roleFilters' => UserRoleFilterOptions::build($this->roles->allNames()),
            'totalPages' => max(1, (int) ceil($users->total / max(1, $users->perPage))),
        ]);
    }
}
