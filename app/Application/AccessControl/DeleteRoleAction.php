<?php

declare(strict_types=1);

namespace App\Application\AccessControl;

use App\Application\AccessControl\Exceptions\RoleNotFound;
use App\Domain\AccessControl\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteRoleAction
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function execute(int $roleId): void
    {
        try {
            $this->roles->delete($roleId);
        } catch (ModelNotFoundException) {
            throw RoleNotFound::forId($roleId);
        }
    }
}
