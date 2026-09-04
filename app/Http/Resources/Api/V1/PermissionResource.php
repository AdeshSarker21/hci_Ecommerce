<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class PermissionResource extends Resource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'group' => $this->group,
            'description' => $this->description,
        ];
    }
}
