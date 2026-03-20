<?php

declare(strict_types=1);

namespace App\Application\AccessControl;

use App\Domain\AccessControl\Repositories\RoleRepository;

final readonly class GetRoleSuggestionsAction
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(string $query, int $limit = 8): array
    {
        return $this->roles->suggest($query, $limit);
    }
}
