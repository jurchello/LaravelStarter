<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetPaginatedAbTestsAction;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminAbTestsController extends Controller
{
    public function __construct(
        private readonly GetPaginatedAbTestsAction $getTests,
    ) {}

    public function index(Request $request): View
    {
        $query = AbTestListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
            status: $request->string('status', 'all')->toString(),
        );
        $tests = $this->getTests->execute($query);

        return view('admin-panel.ab-tests.index', [
            'tests' => $tests,
            'query' => $query,
            'totalPages' => max(1, (int) ceil($tests->total / max(1, $tests->perPage))),
        ]);
    }
}
