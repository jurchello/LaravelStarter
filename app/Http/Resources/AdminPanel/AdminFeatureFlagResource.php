<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use App\Domain\FeatureFlags\Entities\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FeatureFlag */
final class AdminFeatureFlagResource extends JsonResource
{
    /**
     * @return array{id: int, key: string, name: string, description: ?string, enabled: bool, rolloutPercent: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'key' => $this->resource->key,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'enabled' => $this->resource->enabled,
            'rolloutPercent' => $this->resource->rolloutPercent,
        ];
    }
}
