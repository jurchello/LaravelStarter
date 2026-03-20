<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use App\Domain\AbTesting\ReadModels\AbTestListItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AbTestListItem */
final class AdminAbTestListItemResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, slug: string, status: string, trafficPercent: int, variantsCount: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'status' => $this->resource->status,
            'trafficPercent' => $this->resource->trafficPercent,
            'variantsCount' => $this->resource->variantsCount,
        ];
    }
}
