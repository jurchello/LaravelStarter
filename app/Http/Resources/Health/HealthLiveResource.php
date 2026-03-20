<?php

declare(strict_types=1);

namespace App\Http\Resources\Health;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class HealthLiveResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{status: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => (string) $this->resource['status'],
        ];
    }
}
