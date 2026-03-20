<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use App\Domain\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class AdminUserResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, email: string, isAdmin: bool, isSuperadmin: bool, roles: array<int, string>, registeredAt: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'isAdmin' => $this->resource->isAdmin,
            'isSuperadmin' => $this->resource->isSuperadmin,
            'roles' => $this->resource->roles,
            'registeredAt' => $this->resource->registeredAt->format(DATE_ATOM),
        ];
    }
}
