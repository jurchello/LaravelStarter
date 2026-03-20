<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RedirectTargetResource extends JsonResource
{
    /**
     * @return array{redirectTo: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'redirectTo' => (string) $this->resource['redirectTo'],
        ];
    }
}
