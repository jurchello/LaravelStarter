<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\Repositories\UserRepository;

final readonly class GetUserSearchSuggestionsAction
{
    public function __construct(
        private UserRepository $users,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(string $query, int $limit = 8): array
    {
        return $this->users->suggest($query, $limit);
    }
}
