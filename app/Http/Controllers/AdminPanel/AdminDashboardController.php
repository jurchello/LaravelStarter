<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AdminPanel\GetDashboardStatsAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly GetDashboardStatsAction $getStats,
    ) {}

    public function index(): View
    {
        return view('admin-panel.dashboard', [
            'stats' => $this->getStats->execute(),
        ]);
    }
}
