<?php

declare(strict_types=1);

namespace App\Support\AdminPanel;

final class UserRoleFilterOptions
{
    /**
     * @param array<int, string> $roleNames
     * @return array<int, array{value: string, label: string}>
     */
    public static function build(array $roleNames): array
    {
        $filters = [
            ['value' => 'all', 'label' => 'All roles'],
            ['value' => 'superadmin', 'label' => 'Superadmin'],
            ['value' => 'unassigned', 'label' => 'No roles'],
        ];

        foreach ($roleNames as $roleName) {
            $filters[] = [
                'value' => $roleName,
                'label' => $roleName,
            ];
        }

        return $filters;
    }
}
