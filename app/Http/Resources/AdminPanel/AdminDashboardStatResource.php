<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminDashboardStatResource extends JsonResource
{
    /**
     * @return array{label: string, value: int, tone: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => (string) $this->resource['label'],
            'value' => (int) $this->resource['value'],
            'tone' => (string) $this->resource['tone'],
        ];
    }
}
