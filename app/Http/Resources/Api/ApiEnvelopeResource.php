<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class ApiEnvelopeResource extends JsonResource
{
    public function __construct(
        private readonly mixed $data,
        private readonly mixed $meta = [],
        private readonly array $errors = [],
    ) {
        parent::__construct(null);
    }

    /**
     * @return array{data: mixed, meta: mixed, errors: array<int, string>}
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->normalize($this->data, $request),
            'meta' => $this->normalize($this->meta, $request),
            'errors' => $this->errors,
        ];
    }

    private function normalize(mixed $value, Request $request): mixed
    {
        if ($value instanceof JsonResource || $value instanceof AnonymousResourceCollection) {
            return $value->resolve($request);
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalize($item, $request);
            }

            return $normalized;
        }

        return $value;
    }
}
