<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin-panel.dashboard');
    }
}
