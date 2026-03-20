<?php

declare(strict_types=1);

namespace App\Http\Resources\AbTesting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TrackEventResultResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{ok: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'ok' => (bool) $this->resource['ok'],
        ];
    }
}
