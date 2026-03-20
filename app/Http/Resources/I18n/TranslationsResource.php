<?php

declare(strict_types=1);

namespace App\Http\Resources\I18n;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TranslationsResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
