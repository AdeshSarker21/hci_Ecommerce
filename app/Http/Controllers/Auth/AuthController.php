<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
            'is_active' => true,
        ]);

        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole);
        }

        Auth::login($user);

        $token = $this->createApiToken($user, 'auth-token');

        return $this->successResponse([
            'user' => new \App\Http\Resources\Api\V1\UserResource($user->load('roles')),
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Your account has been deactivated.', 403);
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user);

        $token = $this->createApiToken($user, 'auth-token');

        return $this->successResponse([
            'user' => new \App\Http\Resources\Api\V1\UserResource($user->load('roles')),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $token = $request->bearerToken();
            if ($token) {
                ApiToken::where('user_id', $user->id)
                    ->where('token', hash('sha256', $token))
                    ->delete();
            }
        }

        Auth::logout();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles.permissions');

        return $this->successResponse(
            new \App\Http\Resources\Api\V1\UserResource($user)
        );
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'locale' => ['sometimes', 'nullable', 'string', 'max:10'],
        ]);

        $user->update($validated);

        return $this->successResponse(
            new \App\Http\Resources\Api\V1\UserResource($user->fresh()->load('roles'))
        );
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }

    protected function createApiToken(User $user, string $name): string
    {
        $plainToken = bin2hex(random_bytes(32));

        $user->apiTokens()->create([
            'token' => hash('sha256', $plainToken),
            'name' => $name,
            'abilities' => ['*'],
            'expires_at' => now()->addDays(30),
        ]);

        return $plainToken;
    }
}
