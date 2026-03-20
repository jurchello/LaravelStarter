<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Http\Resources\Api\ApiEnvelopeResource;
use Illuminate\Http\JsonResponse;

trait RespondsWithApiEnvelope
{
    protected function respond(mixed $data, mixed $meta = [], array $errors = [], int $status = 200): JsonResponse
    {
        return (new ApiEnvelopeResource(
            data: $data,
            meta: $meta,
            errors: $errors,
        ))->response()->setStatusCode($status);
    }
}
