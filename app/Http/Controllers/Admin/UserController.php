<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $role));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::ordered()->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load('roles.permissions');

        return view('admin.users.show', compact('user'));
    }
}
