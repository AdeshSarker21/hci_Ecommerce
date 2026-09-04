<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::ordered()->get()->groupBy('group');

        return view('admin.permissions.index', compact('permissions'));
    }
}
