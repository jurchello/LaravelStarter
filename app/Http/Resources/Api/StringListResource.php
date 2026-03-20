<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StringListResource extends JsonResource
{
    /**
     * @return array{items: array<int, string>}
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => array_values($this->resource),
        ];
    }
}
