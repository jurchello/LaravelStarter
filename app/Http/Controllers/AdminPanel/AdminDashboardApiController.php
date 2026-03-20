<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AdminPanel\GetDashboardStatsAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminDashboardStatResource;
use Illuminate\Http\JsonResponse;

final class AdminDashboardApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetDashboardStatsAction $getStats,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->respond([
            'stats' => AdminDashboardStatResource::collection($this->getStats->execute()),
        ]);
    }
}
