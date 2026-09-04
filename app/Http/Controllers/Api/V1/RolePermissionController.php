<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Permission;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePermissionController extends BaseController
{
    use ApiResponse;

    public function indexRoles(): JsonResponse
    {
        $roles = Role::active()->ordered()->with('permissions')->get();

        return $this->successResponse($roles);
    }

    public function storeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        return $this->successResponse(
            $role->load('permissions'),
            'Role created successfully',
            201
        );
    }

    public function showRole(Role $role): JsonResponse
    {
        $role->load('permissions');

        return $this->successResponse($role);
    }

    public function updateRole(Request $request, Role $role): JsonResponse
    {
        if ($role->is_system) {
            return $this->errorResponse('Cannot modify system roles.', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->update(collect($validated)->except('permission_ids')->toArray());

        if (array_key_exists('permission_ids', $validated)) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        return $this->successResponse(
            $role->fresh()->load('permissions'),
            'Role updated successfully'
        );
    }

    public function destroyRole(Role $role): JsonResponse
    {
        if ($role->is_system) {
            return $this->errorResponse('Cannot delete system roles.', 403);
        }

        if ($role->users()->exists()) {
            return $this->errorResponse('Cannot delete role with assigned users.', 409);
        }

        $role->delete();

        return $this->successResponse(null, 'Role deleted successfully');
    }

    public function indexPermissions(): JsonResponse
    {
        $permissions = Permission::ordered()->get()->groupBy('group');

        return $this->successResponse($permissions);
    }

    public function storePermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions'],
            'group' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $permission = Permission::create($validated);

        return $this->successResponse(
            $permission,
            'Permission created successfully',
            201
        );
    }

    public function updatePermission(Request $request, Permission $permission): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'group' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update($validated);

        return $this->successResponse(
            $permission->fresh(),
            'Permission updated successfully'
        );
    }

    public function destroyPermission(Permission $permission): JsonResponse
    {
        if ($permission->roles()->exists()) {
            return $this->errorResponse('Cannot delete permission assigned to roles.', 409);
        }

        $permission->delete();

        return $this->successResponse(null, 'Permission deleted successfully');
    }

    public function assignRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = \App\Models\User::find($validated['user_id']);
        $user->roles()->syncWithoutDetaching($validated['role_id']);

        return $this->successResponse(
            $user->load('roles'),
            'Role assigned successfully'
        );
    }

    public function removeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = \App\Models\User::find($validated['user_id']);
        $user->roles()->detach($validated['role_id']);

        return $this->successResponse(
            $user->load('roles'),
            'Role removed successfully'
        );
    }
}
