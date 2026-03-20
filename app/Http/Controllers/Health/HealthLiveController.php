<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Http\Resources\Health\HealthLiveResource;

final class HealthLiveController
{
    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        return (new HealthLiveResource(['status' => 'ok']))->response();
    }
}
