<?php

declare(strict_types=1);

namespace App\Http\Requests\AbTesting;

use Illuminate\Foundation\Http\FormRequest;

final class TrackEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'test'  => ['required', 'string'],
            'event' => ['required', 'string'],
        ];
    }
}