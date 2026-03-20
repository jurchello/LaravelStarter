<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AccessControl\GetPaginatedRolesAction;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminRolesController extends Controller
{
    public function __construct(
        private readonly GetPaginatedRolesAction $getRoles,
    ) {}

    public function index(Request $request): View
    {
        $query = RoleListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
        );
        $roles = $this->getRoles->execute($query);

        return view('admin-panel.roles.index', [
            'roles' => $roles,
            'query' => $query,
            'totalPages' => max(1, (int) ceil($roles->total / max(1, $roles->perPage))),
        ]);
    }
}
