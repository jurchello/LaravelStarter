<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetPaginatedAbTestAssignmentsAction;
use App\Http\Controllers\Controller;
use App\Models\AbTest;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminAbTestAssignmentsController extends Controller
{
    public function __construct(
        private readonly GetPaginatedAbTestAssignmentsAction $getAssignments,
    ) {}

    public function __invoke(Request $request, AbTest $abTest): View
    {
        $assignments = $this->getAssignments->execute($abTest->id, $request->integer('page', 1), 50);

        return view('admin-panel.ab-tests.assignments', [
            'abTest' => $abTest,
            'assignments' => $assignments,
            'totalPages' => max(1, (int) ceil($assignments->total / max(1, $assignments->perPage))),
        ]);
    }
}
