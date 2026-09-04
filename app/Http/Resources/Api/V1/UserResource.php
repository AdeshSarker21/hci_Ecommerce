<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class UserResource extends Resource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'email_verified' => $this->email_verified_at !== null,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('permissions')->flatten()->unique('slug')->values();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
