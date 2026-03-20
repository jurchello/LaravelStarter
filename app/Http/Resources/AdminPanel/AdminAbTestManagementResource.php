<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AbTestManagementView */
final class AdminAbTestManagementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'status' => $this->resource->status,
            'trafficPercent' => $this->resource->trafficPercent,
            'distributionMode' => $this->resource->distributionMode->value,
            'variants' => AdminAbTestVariantResource::collection($this->resource->variants)->resolve($request),
            'analytics' => [
                'assignmentsCount' => $this->resource->analytics->assignmentsCount,
                'identifiedAssignmentsCount' => $this->resource->analytics->identifiedAssignmentsCount,
                'eventsByName' => $this->resource->analytics->eventsByName,
            ],
            'recentAssignments' => AdminAbTestAssignmentResource::collection($this->resource->recentAssignments)->resolve($request),
            'recentEvents' => AdminAbTestEventResource::collection($this->resource->recentEvents)->resolve($request),
        ];
    }
}
