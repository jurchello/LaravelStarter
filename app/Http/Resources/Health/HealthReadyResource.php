<?php

declare(strict_types=1);

namespace App\Http\Resources\Health;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class HealthReadyResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{status: string, checks: array{db: bool, redis: bool, queue: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => (string) $this->resource['status'],
            'checks' => [
                'db' => (bool) $this->resource['checks']['db'],
                'redis' => (bool) $this->resource['checks']['redis'],
                'queue' => (bool) $this->resource['checks']['queue'],
            ],
        ];
    }
}
