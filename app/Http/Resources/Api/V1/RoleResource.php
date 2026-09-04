<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class RoleResource extends Resource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
