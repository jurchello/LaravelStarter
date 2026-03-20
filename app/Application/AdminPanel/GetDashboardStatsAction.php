<?php

declare(strict_types=1);

namespace App\Application\AdminPanel;

use App\Models\User;

final readonly class GetDashboardStatsAction
{
    /**
     * @return array<int, array{label: string, value: int, tone: 'neutral'|'success'|'accent'|'warning'}>
     */
    public function execute(): array
    {
        $totalUsers = User::query()->count();
        $adminUsers = User::query()->where('is_admin', true)->count();
        $verifiedUsers = User::query()->whereNotNull('email_verified_at')->count();
        $newUsersLastWeek = User::query()
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            [
                'label' => 'Total users',
                'value' => $totalUsers,
                'tone' => 'neutral',
            ],
            [
                'label' => 'Verified users',
                'value' => $verifiedUsers,
                'tone' => 'success',
            ],
            [
                'label' => 'Admins',
                'value' => $adminUsers,
                'tone' => 'accent',
            ],
            [
                'label' => 'New this week',
                'value' => $newUsersLastWeek,
                'tone' => 'warning',
            ],
        ];
    }
}
