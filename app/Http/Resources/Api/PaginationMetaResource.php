<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PaginationMetaResource extends JsonResource
{
    /**
     * @return array{page: int, perPage: int, total: int, totalPages: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'page' => (int) $this->resource['page'],
            'perPage' => (int) $this->resource['perPage'],
            'total' => (int) $this->resource['total'],
            'totalPages' => (int) $this->resource['totalPages'],
        ];
    }
}
