<?php

declare(strict_types=1);

namespace App\Application\AccessControl;

use App\Domain\AccessControl\ReadModels\PaginatedRoles;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\AccessControl\ValueObjects\RoleListQuery;

final readonly class GetPaginatedRolesAction
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function execute(RoleListQuery $query): PaginatedRoles
    {
        return $this->roles->paginate($query);
    }
}
