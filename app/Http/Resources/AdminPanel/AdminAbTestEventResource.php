<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAbTestEventResource extends JsonResource
{
    /**
     * @return array{id: int, event: string, variantName: string, variantSlug: string, visitorId: string, createdAt: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'event' => $this->resource->event,
            'variantName' => $this->resource->variantName,
            'variantSlug' => $this->resource->variantSlug,
            'visitorId' => $this->resource->visitorId,
            'createdAt' => $this->resource->createdAt,
        ];
    }
}
