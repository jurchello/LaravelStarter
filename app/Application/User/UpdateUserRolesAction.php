<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Exceptions\UserNotFound;
use App\Domain\User\Dto\UserRolesData;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class UpdateUserRolesAction
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function execute(int $userId, UserRolesData $data): User
    {
        try {
            return $this->users->syncRoles($userId, $data->roles);
        } catch (ModelNotFoundException) {
            throw UserNotFound::forId($userId);
        }
    }
}
