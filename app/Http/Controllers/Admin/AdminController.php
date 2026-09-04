<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ];

        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        $roles = Role::withCount('users')->ordered()->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'roles'));
    }
}
