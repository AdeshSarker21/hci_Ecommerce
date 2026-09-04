<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->ordered()->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        return view('admin.roles.show', compact('role'));
    }
}
