<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAbTestAudienceEstimateResource extends JsonResource
{
    /**
     * @return array{audienceSize: int, trafficPercent: int, estimatedPeople: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'audienceSize' => (int) $this->resource['audienceSize'],
            'trafficPercent' => (int) $this->resource['trafficPercent'],
            'estimatedPeople' => (int) $this->resource['estimatedPeople'],
        ];
    }
}
