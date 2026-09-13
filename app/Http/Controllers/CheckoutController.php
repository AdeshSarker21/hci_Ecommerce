<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected CartService $cart,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $checkoutData = $this->orderService->getCheckoutData($user);

        if ($checkoutData['cart_items']->isEmpty()) {
            return redirect()->route('cart.index')->with('info', __('Your cart is empty.'));
        }

        return view('checkout.index', array_merge($checkoutData, [
            'user' => $user,
        ]));
    }

    public function validateCart(): JsonResponse
    {
        $user = auth()->user();
        $errors = $this->orderService->validateCartItems($user);

        return response()->json([
            'success' => empty($errors),
            'errors' => $errors,
        ]);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,online',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        if ($request->address_id && !$user->addresses()->find($request->address_id)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid address selected.'),
            ], 422);
        }

        $result = $this->orderService->placeOrder(
            $user,
            $request->address_id,
            $request->payment_method,
            session('coupon_code'),
            $request->notes
        );

        $status = $result['success'] ? 200 : 422;
        return response()->json($result, $status);
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

        if ($validated['is_default']) {
            $address->setAsDefault();
        }

        if ($user->addresses()->count() === 1) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'message' => __('Address saved successfully.'),
            'address' => $address->toArray(),
        ]);
    }

    public function updateAddress(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => __('Address not found.'),
            ], 404);
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
            return response()->json([
                'success' => false,
                'message' => __('Address not found.'),
            ], 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Address deleted successfully.'),
        ]);
    }

    public function setDefaultAddress(int $id): JsonResponse
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => __('Address not found.'),
            ], 404);
        }

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => __('Default address updated.'),
        ]);
    }
}
