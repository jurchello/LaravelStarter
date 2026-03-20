<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\User\GetUserSearchSuggestionsAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StringListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUserSuggestionsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetUserSearchSuggestionsAction $suggestions,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim($request->string('query')->toString());

        return $this->respond(new StringListResource(
            $query !== '' ? $this->suggestions->execute($query) : [],
        ));
    }
}
