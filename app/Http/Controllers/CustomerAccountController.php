<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();

        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing_orders' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'shipped_orders' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'wishlist_count' => Wishlist::where('user_id', $user->id)->count(),
        ];

        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product', 'seller'])
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        return view('account.dashboard', compact('user', 'stats', 'recentOrders'));
    }

    public function profile(): View
    {
        $user = auth()->user();
        return view('account.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
            if ($user->avatar && $user->avatar !== $path) {
                \Storage::disk('public')->delete($user->avatar);
            }
        }

        $user->update($validated);

        return redirect()->route('account.profile')->with('success', __('Profile updated successfully.'));
    }

    public function password(): View
    {
        $user = auth()->user();
        return view('account.password', compact('user'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('The provided password does not match your current password.')]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('account.password')->with('success', __('Password updated successfully.'));
    }

    public function addresses(): View
    {
        $user = auth()->user();
        $addresses = $user->addresses()->get();

        return view('account.addresses', compact('user', 'addresses'));
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['is_default'] = $validated['is_default'] ?? false;

        $address = Address::create($validated);

        if ($validated['is_default'] || $user->addresses()->count() === 1) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'message' => __('Address saved successfully.'),
            'address' => $address->fresh()->toArray(),
        ]);
    }

    public function updateAddress(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => __('Address not found.')], 404);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $address->update($validated);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'message' => __('Address updated successfully.'),
            'address' => $address->fresh()->toArray(),
        ]);
    }

    public function deleteAddress(int $id): JsonResponse
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => __('Address not found.')], 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => __('Address deleted successfully.')]);
    }

    public function setDefaultAddress(int $id): JsonResponse
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json(['success' => false, 'message' => __('Address not found.')], 404);
        }

        $address->setAsDefault();

        return response()->json(['success' => true, 'message' => __('Default address updated.')]);
    }

    public function orders(): View
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'seller'])
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'shipped' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
        ];

        return view('account.orders-list', compact('orders', 'stats'));
    }

    public function showOrder(Order $order): View
    {
        $user = auth()->user();

        abort_unless($order->user_id === $user->id, 403);

        $order->load(['items.product.images', 'seller', 'statusHistory']);

        $timeline = $this->buildTimeline($order);

        return view('account.orders.show', compact('order', 'timeline'));
    }

    protected function buildTimeline(Order $order): array
    {
        $steps = [
            ['key' => 'pending', 'label' => __('Order Placed'), 'icon' => 'receipt'],
            ['key' => 'processing', 'label' => __('Confirmed'), 'icon' => 'check-circle'],
            ['key' => 'shipped', 'label' => __('Shipped'), 'icon' => 'truck'],
            ['key' => 'delivered', 'label' => __('Delivered'), 'icon' => 'home'],
        ];

        $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIndex = array_search($order->status, $statusOrder);
        $currentIndex = $currentIndex === false ? -1 : $currentIndex;

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index <= $currentIndex;
            $step['current'] = $index === $currentIndex;
            $step['timestamp'] = match ($step['key']) {
                'pending' => $order->created_at,
                'processing' => $order->processing_at,
                'shipped' => $order->shipped_at,
                'delivered' => $order->delivered_at,
                default => null,
            };
        }

        if ($order->status === 'cancelled') {
            $steps[] = [
                'key' => 'cancelled',
                'label' => __('Cancelled'),
                'icon' => 'x-circle',
                'completed' => true,
                'current' => true,
                'timestamp' => $order->cancelled_at,
            ];
        }

        return $steps;
    }

    public function wishlist(): View
    {
        $user = auth()->user();

        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with(['product.images', 'product.seller', 'product.brand'])
            ->latest()
            ->get();

        $wishlistCount = $wishlistItems->count();

        return view('account.wishlist-list', compact('wishlistItems', 'wishlistCount'));
    }

    public function recentlyViewed(): View
    {
        $user = auth()->user();

        $items = $user->recentlyViewedItems()
            ->with(['product.images', 'product.seller', 'product.brand'])
            ->latest()
            ->limit(20)
            ->get();

        return view('account.recently-viewed', compact('items'));
    }

    public function reviews(): View
    {
        $user = auth()->user();

        $reviews = ProductReview::where('user_id', $user->id)
            ->with(['product.images'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('account.reviews', compact('reviews'));
    }

    public function notifications(): View
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(15);

        return view('account.notifications', compact('notifications'));
    }

    public function wallet(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet ?? null;

        return view('account.wallet', compact('user', 'wallet'));
    }

    public function settings(): View
    {
        $user = auth()->user();
        return view('account.settings', compact('user'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'timezone' => 'nullable|string|max:50',
            'locale' => 'required|in:en,bn',
        ]);

        $user->update($validated);

        app()->setLocale($user->locale ?? 'en');

        return redirect()->route('account.settings')->with('success', __('Settings updated successfully.'));
    }
}
