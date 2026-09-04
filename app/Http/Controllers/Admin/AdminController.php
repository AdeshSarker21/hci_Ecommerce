<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'           => User::count(),
            'active_users'    => User::where('is_active', true)->count(),
            'roles'           => Role::count(),
            'permissions'     => Permission::count(),
            'sellers'         => class_exists(Seller::class) ? Seller::count() : 0,
            'pending_sellers' => class_exists(Seller::class) ? Seller::where('status', 'pending')->count() : 0,
            'products'        => 0,
        ];

        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        $recentSellers = class_exists(Seller::class)
            ? Seller::with('user')->latest()->take(5)->get()
            : collect();

        $pendingSellers = class_exists(Seller::class)
            ? Seller::where('status', 'pending')->with('user')->take(5)->get()
            : collect();

        $roles = Role::withCount('users')->ordered()->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentSellers', 'pendingSellers', 'roles'));
    }
}
