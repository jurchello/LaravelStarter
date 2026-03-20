<?php

declare(strict_types=1);

namespace App\Http\Resources\AbTesting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AssignedVariantResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{variant: ?string}
     */
    public function toArray(Request $request): array
    {
        return [
            'variant' => $this->resource['variant'],
        ];
    }
}
