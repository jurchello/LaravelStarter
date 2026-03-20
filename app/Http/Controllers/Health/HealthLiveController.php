<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Http\Resources\Health\HealthLiveResource;
use Illuminate\Http\JsonResponse;

final class HealthLiveController
{
    public function __invoke(): JsonResponse
    {
        return (new HealthLiveResource(['status' => 'ok']))->response();
    }
}
