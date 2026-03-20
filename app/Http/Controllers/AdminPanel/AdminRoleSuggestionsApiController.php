<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AccessControl\GetRoleSuggestionsAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StringListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRoleSuggestionsApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetRoleSuggestionsAction $suggestions,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim($request->string('query')->toString());

        return $this->respond(new StringListResource(
            $query !== '' ? $this->suggestions->execute($query) : [],
        ));
    }
}
