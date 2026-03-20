<?php

declare(strict_types=1);

namespace App\Http\Resources\AdminPanel;

use App\Domain\AccessControl\Entities\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
final class AdminRoleResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, usersCount: int, permissions: array<int, string>}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'usersCount' => $this->resource->usersCount,
            'permissions' => $this->resource->permissions,
        ];
    }
}
