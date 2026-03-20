<?php

declare(strict_types=1);

namespace App\Application\AccessControl;

use App\Application\AccessControl\Exceptions\RoleNotFound;
use App\Domain\AccessControl\Dto\RoleData;
use App\Domain\AccessControl\Entities\Role;
use App\Domain\AccessControl\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class UpdateRoleAction
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function execute(int $roleId, RoleData $data): Role
    {
        try {
            return $this->roles->update($roleId, $data);
        } catch (ModelNotFoundException) {
            throw RoleNotFound::forId($roleId);
        }
    }
}
