<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAbTestVariantResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, slug: string, weight: int, assignmentsCount: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'weight' => $this->resource->weight,
            'assignmentsCount' => $this->resource->assignmentsCount,
        ];
    }
}
