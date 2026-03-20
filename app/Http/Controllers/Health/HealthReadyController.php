<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Application\Health\GetReadinessAction;
use App\Http\Resources\Health\HealthReadyResource;

final class HealthReadyController
{
    public function __invoke(GetReadinessAction $action): \Illuminate\Http\JsonResponse
    {
        $payload = $action->execute();

        return (new HealthReadyResource($payload))
            ->response()
            ->setStatusCode($payload['status'] === 'ok' ? 200 : 503);
    }
}
