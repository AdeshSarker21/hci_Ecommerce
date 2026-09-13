<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class WebAuthController extends Controller
{
    public function __construct(
        protected CartService $cart,
    ) {}

    public function showLogin(Request $request)
    {
        return view('auth.login', [
            'intendedUrl' => $request->query('intended'),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ])->onlyInput('email');
        }

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        app(\App\Services\RecentlyViewedService::class)->mergeGuestData($user->id);

        $this->cart->mergeGuestCartToUser($user->id);

        return redirect()->intended($this->redirectToRole($user));
    }

    public function showRegister(Request $request)
    {
        return view('auth.register', [
            'intendedUrl' => $request->query('intended'),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'active',
            'is_active' => true,
        ]);

        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole);
        }

        Auth::login($user);

        $request->session()->regenerate();

        app(\App\Services\RecentlyViewedService::class)->mergeGuestData($user->id);

        $this->cart->mergeGuestCartToUser($user->id);

        return redirect()->intended($this->redirectToRole($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Determine redirect path based on user role.
     */
    protected function redirectToRole(User $user): string
    {
        if ($user->hasAnyRole(['super-admin', 'admin', 'manager', 'product-manager'])) {
            return route('admin.dashboard');
        }

        if ($user->hasAnyRole(['seller', 'seller-staff'])) {
            return route('seller.dashboard');
        }

        return route('dashboard');
    }

    /**
     * Validate that a URL is safe for redirect (same host, not external).
     */
    protected function isValidRedirectUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $currentHost = request()->getHost();

        return $host && $host === $currentHost;
    }
}
