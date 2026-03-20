<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetAbTestAudienceEstimateAction;
use App\Application\AbTesting\GetAbTestManagementViewAction;
use App\Application\AbTesting\GetPaginatedAbTestAssignmentsAction;
use App\Application\AbTesting\GetPaginatedAbTestEventsAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminAbTestAssignmentResource;
use App\Http\Resources\AdminPanel\AdminAbTestAudienceEstimateResource;
use App\Http\Resources\AdminPanel\AdminAbTestEventResource;
use App\Http\Resources\AdminPanel\AdminAbTestManagementResource;
use App\Http\Resources\Api\PaginationMetaResource;
use App\Models\AbTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminAbTestInsightsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetPaginatedAbTestAssignmentsAction $getAssignments,
        private readonly GetPaginatedAbTestEventsAction $getEvents,
        private readonly GetAbTestManagementViewAction $getAnalytics,
        private readonly GetAbTestAudienceEstimateAction $getAudienceEstimate,
    ) {}

    public function audienceEstimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trafficPercent' => ['required', 'integer', 'between:0,100'],
        ]);

        return $this->respond(new AdminAbTestAudienceEstimateResource(
            $this->getAudienceEstimate->execute((int) $validated['trafficPercent']),
        ));
    }

    public function assignments(Request $request, AbTest $abTest): JsonResponse
    {
        $result = $this->getAssignments->execute($abTest->id, $request->integer('page', 1), 50);

        return $this->respond(
            data: [
                'items' => AdminAbTestAssignmentResource::collection($result->items),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => (int) ceil($result->total / max(1, $result->perPage)),
            ]),
        );
    }

    public function events(Request $request, AbTest $abTest): JsonResponse
    {
        $result = $this->getEvents->execute($abTest->id, $request->integer('page', 1), 50);

        return $this->respond(
            data: [
                'items' => AdminAbTestEventResource::collection($result->items),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => (int) ceil($result->total / max(1, $result->perPage)),
            ]),
        );
    }

    public function analytics(AbTest $abTest): JsonResponse
    {
        return $this->respond(new AdminAbTestManagementResource(
            $this->getAnalytics->execute($abTest->id),
        ));
    }
}
