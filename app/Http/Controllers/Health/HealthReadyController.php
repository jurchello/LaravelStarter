<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Application\Health\GetReadinessAction;
use App\Http\Resources\Health\HealthReadyResource;
use Illuminate\Http\JsonResponse;

final class HealthReadyController
{
    public function __invoke(GetReadinessAction $action): JsonResponse
    {
        $payload = $action->execute();

        return (new HealthReadyResource($payload))
            ->response()
            ->setStatusCode($payload['status'] === 'ok' ? 200 : 503);
    }
}
