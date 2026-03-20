<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetAbTestManagementViewAction;
use App\Http\Controllers\Controller;
use App\Models\AbTest;
use Illuminate\View\View;

final class AdminAbTestManagementController extends Controller
{
    public function __construct(
        private readonly GetAbTestManagementViewAction $getTest,
    ) {}

    public function __invoke(AbTest $abTest): View
    {
        return view('admin-panel.ab-tests.show', [
            'abTest' => $abTest,
            'testView' => $this->getTest->execute($abTest->id),
        ]);
    }
}
