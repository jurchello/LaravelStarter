<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\GetPaginatedAbTestsAction;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminAbTestListItemResource;
use App\Http\Resources\Api\PaginationMetaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminAbTestsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetPaginatedAbTestsAction $getTests,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = AbTestListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
            status: $request->string('status', 'all')->toString(),
        );
        $result = $this->getTests->execute($query);

        return $this->respond(
            data: [
                'items' => AdminAbTestListItemResource::collection($result->items),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => (int) ceil($result->total / max(1, $result->perPage)),
            ]),
        );
    }
}
