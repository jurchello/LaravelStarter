<?php

declare(strict_types=1);

namespace App\Application\AccessControl;

use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Entities\Role;
use App\Domain\AccessControl\Repositories\RoleRepository;

final readonly class CreateRoleAction
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function execute(RoleData $data): Role
    {
        return $this->roles->create($data);
    }
}
