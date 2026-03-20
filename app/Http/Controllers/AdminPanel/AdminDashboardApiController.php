<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminDashboardStatResource;
use App\Models\User;

final class AdminDashboardApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        $totalUsers = User::query()->count();
        $adminUsers = User::query()->where('is_admin', true)->count();
        $verifiedUsers = User::query()->whereNotNull('email_verified_at')->count();
        $newUsersLastWeek = User::query()
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return $this->respond([
            'stats' => AdminDashboardStatResource::collection([
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
                ]),
        ]);
    }
}
