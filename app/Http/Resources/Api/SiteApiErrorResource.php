<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SiteApiErrorResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param  array<string, mixed>  $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'message' => (string) ($this->resource['message'] ?? 'An unexpected error occurred.'),
        ];

        if (array_key_exists('errors', $this->resource) && is_array($this->resource['errors']) && $this->resource['errors'] !== []) {
            $payload['errors'] = $this->resource['errors'];
        }

        return $payload;
    }
}
