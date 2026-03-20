<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AbTest;
use Illuminate\View\View;

final class AdminAbTestAssignmentsController extends Controller
{
    public function __invoke(AbTest $abTest): View
    {
        return view('admin-panel.ab-tests.assignments', [
            'abTest' => $abTest,
        ]);
    }
}
