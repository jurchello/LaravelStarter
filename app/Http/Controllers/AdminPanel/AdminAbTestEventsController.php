<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetPaginatedAbTestEventsAction;
use App\Http\Controllers\Controller;
use App\Models\AbTest;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminAbTestEventsController extends Controller
{
    public function __construct(
        private readonly GetPaginatedAbTestEventsAction $getEvents,
    ) {}

    public function __invoke(Request $request, AbTest $abTest): View
    {
        $events = $this->getEvents->execute($abTest->id, $request->integer('page', 1), 50);

        return view('admin-panel.ab-tests.events', [
            'abTest' => $abTest,
            'events' => $events,
            'totalPages' => max(1, (int) ceil($events->total / max(1, $events->perPage))),
        ]);
    }
}
