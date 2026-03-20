<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAbTestAssignmentResource extends JsonResource
{
    /**
     * @return array{id: int, visitorId: string, userId: ?int, variantName: string, variantSlug: string, createdAt: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'visitorId' => $this->resource->visitorId,
            'userId' => $this->resource->userId,
            'variantName' => $this->resource->variantName,
            'variantSlug' => $this->resource->variantSlug,
            'createdAt' => $this->resource->createdAt,
        ];
    }
}
