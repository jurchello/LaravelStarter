<?php

declare(strict_types=1);

namespace App\Http\Support\Api;

use Illuminate\Http\Request;

final class ApiRequestClassifier
{
    public function isAdmin(Request $request): bool
    {
        return $request->is('management/api/*');
    }

    public function isSite(Request $request): bool
    {
        return $request->is('api/*');
    }

    public function isApi(Request $request): bool
    {
        return $this->isAdmin($request) || $this->isSite($request);
    }
}
