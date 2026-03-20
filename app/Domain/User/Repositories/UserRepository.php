<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\ReadModels\PaginatedUsers;
use App\Domain\User\ValueObjects\UserListQuery;

interface UserRepository
{
    public function paginate(UserListQuery $query): PaginatedUsers;

    public function countAudience(): int;

    /**
     * @param array<int, string> $roles
     */
    public function syncRoles(int $userId, array $roles): \App\Domain\User\Entities\User;

    /**
     * @return array<int, string>
     */
    public function suggest(string $query, int $limit = 8): array;
}
