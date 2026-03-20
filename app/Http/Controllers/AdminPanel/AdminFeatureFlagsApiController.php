<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Events\AdminPanel\FeatureFlagsChanged;
use App\Application\FeatureFlags\CreateFeatureFlagAction;
use App\Application\FeatureFlags\DeleteFeatureFlagAction;
use App\Application\FeatureFlags\GetPaginatedFeatureFlagsAction;
use App\Application\FeatureFlags\UpdateFeatureFlagAction;
use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminFeatureFlagResource;
use App\Http\Resources\Api\PaginationMetaResource;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminFeatureFlagsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetPaginatedFeatureFlagsAction $getFlags,
        private readonly CreateFeatureFlagAction $createFlag,
        private readonly UpdateFeatureFlagAction $updateFlag,
        private readonly DeleteFeatureFlagAction $deleteFlag,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = FeatureFlagListQuery::fromScalars(
            page: $request->integer('page', 1),
            perPage: 50,
            sortBy: $request->string('sort', 'name')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
            search: $request->string('search')->toString(),
            status: $request->string('status', 'all')->toString(),
        );
        $result = $this->getFlags->execute($query);

        return $this->respond(
            data: [
                'items' => AdminFeatureFlagResource::collection($result->items),
            ],
            meta: new PaginationMetaResource([
                'page' => $result->currentPage,
                'perPage' => $result->perPage,
                'total' => $result->total,
                'totalPages' => max(1, (int) ceil($result->total / max(1, $result->perPage))),
            ]),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $flag = $this->createFlag->execute($this->dataFromRequest($request));
        broadcast(new FeatureFlagsChanged('created'))->toOthers();

        return $this->respond([
            'flag' => new AdminFeatureFlagResource($flag),
        ], status: 201);
    }

    public function update(Request $request, FeatureFlag $featureFlag): JsonResponse
    {
        $flag = $this->updateFlag->execute((int) $featureFlag->id, $this->dataFromRequest($request, $featureFlag));
        broadcast(new FeatureFlagsChanged('updated'))->toOthers();

        return $this->respond([
            'flag' => new AdminFeatureFlagResource($flag),
        ]);
    }

    public function destroy(FeatureFlag $featureFlag): JsonResponse
    {
        $this->deleteFlag->execute((int) $featureFlag->id);
        broadcast(new FeatureFlagsChanged('deleted'))->toOthers();

        return $this->respond([
            'deleted' => true,
        ]);
    }

    private function dataFromRequest(Request $request, ?FeatureFlag $featureFlag = null): FeatureFlagData
    {
        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('feature_flags', 'key')->ignore($featureFlag?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'enabled' => ['required', 'boolean'],
            'rolloutPercent' => ['required', 'integer', 'between:0,100'],
        ]);

        return new FeatureFlagData(
            key: $validated['key'],
            name: $validated['name'],
            description: $validated['description'] ?? null,
            enabled: (bool) $validated['enabled'],
            rolloutPercent: (int) $validated['rolloutPercent'],
        );
    }
}
