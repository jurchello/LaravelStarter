<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\FeatureFlags\GetFeatureFlagSuggestionsAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StringListResource;
use Illuminate\Http\Request;

final class AdminFeatureFlagSuggestionsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetFeatureFlagSuggestionsAction $suggestions,
    ) {}

    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim($request->string('query')->toString());

        return $this->respond(new StringListResource(
            $query !== '' ? $this->suggestions->execute($query) : [],
        ));
    }
}
