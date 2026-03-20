<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\FeatureFlags\GetPaginatedFeatureFlagsAction;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminFeatureFlagsController extends Controller
{
    public function __construct(
        private readonly GetPaginatedFeatureFlagsAction $getFlags,
    ) {}

    public function index(Request $request): View
    {
        $query = FeatureFlagListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
            status: $request->string('status', 'all')->toString(),
        );
        $flags = $this->getFlags->execute($query);

        return view('admin-panel.feature-flags.index', [
            'flags' => $flags,
            'query' => $query,
            'totalPages' => max(1, (int) ceil($flags->total / max(1, $flags->perPage))),
        ]);
    }
}
