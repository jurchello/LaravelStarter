<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\ReadModels\PaginatedUsers;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\UserListQuery;

final readonly class GetPaginatedUsersAction
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function execute(UserListQuery $query): PaginatedUsers
    {
        return $this->users->paginate($query);
    }
}
