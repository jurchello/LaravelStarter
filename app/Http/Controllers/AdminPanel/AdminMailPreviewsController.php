<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class AdminMailPreviewsController extends Controller
{
    public function index(): View
    {
        return view('admin-panel.mail-previews.index');
    }
}
