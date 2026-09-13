<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCommissionController;
use App\Http\Controllers\Admin\AdminCourierController;
use App\Http\Controllers\Admin\AdminCourierManagementController;
use App\Http\Controllers\Admin\AdminSellerPaymentController;
use App\Http\Controllers\Admin\AdminShipmentController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryAttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\ProductSearchController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\StorefrontReviewController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerCommissionController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Seller\SellerInventoryController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerSettlementController;
use App\Http\Controllers\Seller\SellerStaffController;
use App\Http\Controllers\Seller\SellerWalletController;
use App\Http\Controllers\Seller\SellerWarehouseController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [WebAuthController::class, 'login'])->middleware(['guest', 'throttle.login']);
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [WebAuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/dashboard', [CustomerAccountController::class, 'dashboard'])->name('dashboard');

    // Customer Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/profile', [CustomerAccountController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
        Route::get('/password', [CustomerAccountController::class, 'password'])->name('password');
        Route::put('/password', [CustomerAccountController::class, 'updatePassword'])->name('password.update');
        Route::get('/addresses', [CustomerAccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [CustomerAccountController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{id}', [CustomerAccountController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{id}', [CustomerAccountController::class, 'deleteAddress'])->name('addresses.delete');
        Route::post('/addresses/{id}/default', [CustomerAccountController::class, 'setDefaultAddress'])->name('addresses.default');
        Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [CustomerAccountController::class, 'showOrder'])->name('orders.show');

        // Customer Shipment Tracking
        Route::get('/orders/{order}/track', [\App\Http\Controllers\CustomerShipmentController::class, 'track'])->name('orders.track');

        Route::get('/wishlist', [CustomerAccountController::class, 'wishlist'])->name('wishlist');
        Route::get('/recently-viewed', [CustomerAccountController::class, 'recentlyViewed'])->name('recently-viewed');
        Route::get('/reviews', [CustomerAccountController::class, 'reviews'])->name('reviews');
        Route::get('/notifications', [CustomerAccountController::class, 'notifications'])->name('notifications');
        Route::get('/wallet', [CustomerAccountController::class, 'wallet'])->name('wallet');
        Route::get('/settings', [CustomerAccountController::class, 'settings'])->name('settings');
        Route::put('/settings', [CustomerAccountController::class, 'updateSettings'])->name('settings.update');
    });

    // Legacy customer orders routes (redirect to account)
    Route::prefix('account/orders-old')->name('account.orders-old.')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [CustomerOrderController::class, 'show'])->name('show');
        Route::get('/{order}/confirmation', [CustomerOrderController::class, 'confirmation'])->name('confirmation');
    });
});

// Public Product Search
Route::get('/search', [ProductSearchController::class, 'index'])->name('search');
Route::get('/product/{slug}', [ProductSearchController::class, 'show'])->name('product.show');

// Product Reviews
Route::post('/reviews', [StorefrontReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// Public Category Listing
Route::get('/category/{slug}', [StorefrontCategoryController::class, 'show'])->name('category.show');

// Public Seller Storefront
Route::get('/store/{slug}', [StorefrontController::class, 'show'])->name('storefront.show');

// Shopping Cart (guest + authenticated)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/revalidate', [CartController::class, 'revalidate'])->name('cart.revalidate');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Checkout (authenticated only)
Route::middleware(['auth', 'ensure.active'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('place-order');
    Route::get('/validate-cart', [CheckoutController::class, 'validateCart'])->name('validate-cart');
    Route::post('/address', [CheckoutController::class, 'storeAddress'])->name('address.store');
    Route::put('/address/{id}', [CheckoutController::class, 'updateAddress'])->name('address.update');
    Route::delete('/address/{id}', [CheckoutController::class, 'deleteAddress'])->name('address.delete');
    Route::post('/address/{id}/default', [CheckoutController::class, 'setDefaultAddress'])->name('address.default');
});

// Wishlist (authenticated only)
Route::middleware(['auth', 'ensure.active'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/toggle/{id}', [WishlistController::class, 'toggle'])->name('toggle');
    Route::delete('/{id}', [WishlistController::class, 'remove'])->name('remove');
    Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
    Route::post('/move-to-cart/{id}', [WishlistController::class, 'moveToCart'])->name('move-to-cart');
    Route::get('/count', [WishlistController::class, 'count'])->name('count');
});

Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle.api')->middleware('auth');
Route::get('/wishlist/check/{id}', [WishlistController::class, 'check'])->name('wishlist.check');
Route::get('/recently-viewed', [WishlistController::class, 'recentlyViewed'])->name('recently-viewed');
Route::get('/recently-viewed/track/{id}', function ($id) {
    app(\App\Services\RecentlyViewedService::class)->track($id);
    return response()->json(['success' => true]);
})->name('recently-viewed.track');

// Courier Webhooks (public - no auth)
Route::post('/webhook/steadfast', [\App\Http\Controllers\WebhookController::class, 'handleSteadfast'])->name('webhook.steadfast');
Route::post('/webhook/pathao', [\App\Http\Controllers\WebhookController::class, 'handlePathao'])->name('webhook.pathao');
Route::post('/webhook/courier/{courierSlug}', [\App\Http\Controllers\WebhookController::class, 'genericWebhook'])->name('webhook.courier');

// API Webhook Routes (public - no auth)
Route::prefix('api/webhooks/courier')->name('api.webhooks.courier.')->group(function () {
    Route::post('/steadfast', [\App\Http\Controllers\WebhookController::class, 'handleSteadfast'])->name('steadfast');
    Route::post('/pathao', [\App\Http\Controllers\WebhookController::class, 'handlePathao'])->name('pathao');
    Route::post('/{courierSlug}', [\App\Http\Controllers\WebhookController::class, 'genericWebhook'])->name('generic');
});

// Admin Panel
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'ensure.active', 'check.role:super-admin,admin,manager,product-manager'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index')
            ->middleware('check.permission:users.view,users.manage');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')
            ->middleware('check.permission:users.view,users.manage');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')
            ->middleware('check.permission:roles.view,roles.manage');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show')
            ->middleware('check.permission:roles.view,roles.manage');

        // Permissions
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')
            ->middleware('check.permission:permissions.view,roles.manage');

        // Sellers
        Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/pending', [AdminSellerController::class, 'pending'])->name('sellers.pending')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/performance', [AdminSellerController::class, 'performance'])->name('sellers.performance')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/{seller}', [AdminSellerController::class, 'show'])->name('sellers.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/{seller}/products', [AdminSellerController::class, 'products'])->name('sellers.products')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/{seller}/orders', [AdminSellerController::class, 'orders'])->name('sellers.orders')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/{seller}/finance', [AdminSellerController::class, 'finance'])->name('sellers.finance')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/sellers/{seller}/activity', [AdminSellerController::class, 'activityLog'])->name('sellers.activity')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::post('/sellers/{seller}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve')
            ->middleware('check.permission:vendors.manage');
        Route::post('/sellers/{seller}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject')
            ->middleware('check.permission:vendors.manage');
        Route::post('/sellers/{seller}/suspend', [AdminSellerController::class, 'suspend'])->name('sellers.suspend')
            ->middleware('check.permission:vendors.manage');
        Route::post('/sellers/{seller}/activate', [AdminSellerController::class, 'activate'])->name('sellers.activate')
            ->middleware('check.permission:vendors.manage');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create')
            ->middleware('check.permission:products.create,products.manage');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store')
            ->middleware('check.permission:products.create,products.manage');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status')
            ->middleware('check.permission:products.update,products.manage');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')
            ->middleware('check.permission:products.delete,products.manage');

        // Brands
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create')
            ->middleware('check.permission:products.create,products.manage');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store')
            ->middleware('check.permission:products.create,products.manage');
        Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('brands.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status')
            ->middleware('check.permission:products.update,products.manage');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy')
            ->middleware('check.permission:products.delete,products.manage');

        // Category Attributes (Specification Fields)
        Route::get('/category-attributes', [CategoryAttributeController::class, 'index'])->name('category-attributes.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/category-attributes/{category}/edit', [CategoryAttributeController::class, 'edit'])->name('category-attributes.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/category-attributes/{category}', [CategoryAttributeController::class, 'update'])->name('category-attributes.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::get('/category-attributes/{category}/attributes', [CategoryAttributeController::class, 'getAttributes'])->name('category-attributes.get-attributes')
            ->middleware('check.permission:products.view,products.manage');

        // Attributes
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create')
            ->middleware('check.permission:products.create,products.manage');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store')
            ->middleware('check.permission:products.create,products.manage');
        Route::get('/attributes/{attribute}', [AttributeController::class, 'show'])->name('attributes.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('attributes.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/attributes/{attribute}/toggle-status', [AttributeController::class, 'toggleStatus'])->name('attributes.toggle-status')
            ->middleware('check.permission:products.update,products.manage');
        Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy')
            ->middleware('check.permission:products.delete,products.manage');
        Route::delete('/attribute-values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy')
            ->middleware('check.permission:products.delete,products.manage');

        // Products
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create')
            ->middleware('check.permission:products.create,products.manage');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store')
            ->middleware('check.permission:products.create,products.manage');
        Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('products.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve')
            ->middleware('check.permission:products.manage');
        Route::post('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject')
            ->middleware('check.permission:products.manage');
        Route::post('/products/{product}/publish', [AdminProductController::class, 'publish'])->name('products.publish')
            ->middleware('check.permission:products.manage');
        Route::post('/products/{product}/unpublish', [AdminProductController::class, 'unpublish'])->name('products.unpublish')
            ->middleware('check.permission:products.manage');

        // Product Review Queue
        Route::get('/review', [ProductReviewController::class, 'index'])->name('review.index')
            ->middleware('check.permission:products.manage');
        Route::get('/review/{product}', [ProductReviewController::class, 'show'])->name('review.show')
            ->middleware('check.permission:products.manage');
        Route::post('/review/{product}/approve', [ProductReviewController::class, 'approve'])->name('review.approve')
            ->middleware('check.permission:products.manage');
        Route::post('/review/{product}/reject', [ProductReviewController::class, 'reject'])->name('review.reject')
            ->middleware('check.permission:products.manage');
        Route::post('/review/{product}/publish', [ProductReviewController::class, 'publish'])->name('review.publish')
            ->middleware('check.permission:products.manage');
        Route::post('/review/{product}/unpublish', [ProductReviewController::class, 'unpublish'])->name('review.unpublish')
            ->middleware('check.permission:products.manage');

        // Inventory
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/inventory/{product}', [AdminInventoryController::class, 'show'])->name('inventory.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::post('/inventory/{product}/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/inventory/{product}/add-stock', [AdminInventoryController::class, 'addStock'])->name('inventory.add-stock')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/inventory/{product}/remove-stock', [AdminInventoryController::class, 'removeStock'])->name('inventory.remove-stock')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/inventory/{product}/generate-barcode', [AdminInventoryController::class, 'generateBarcode'])->name('inventory.generate-barcode')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/inventory/{product}/generate-qr', [AdminInventoryController::class, 'generateQrCode'])->name('inventory.generate-qr')
            ->middleware('check.permission:products.update,products.manage');
        Route::get('/inventory/{product}/print', [AdminInventoryController::class, 'printIdentifiers'])->name('inventory.print')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/inventory/{product}/barcode.svg', [AdminInventoryController::class, 'barcodeSvg'])->name('inventory.barcode-svg')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/inventory/{product}/qr.svg', [AdminInventoryController::class, 'qrCodeSvg'])->name('inventory.qr-svg')
            ->middleware('check.permission:products.view,products.manage');

        // Warehouses
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create')
            ->middleware('check.permission:products.create,products.manage');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store')
            ->middleware('check.permission:products.create,products.manage');
        Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show')
            ->middleware('check.permission:products.view,products.manage');
        Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit')
            ->middleware('check.permission:products.update,products.manage');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update')
            ->middleware('check.permission:products.update,products.manage');
        Route::post('/warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status')
            ->middleware('check.permission:products.update,products.manage');
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy')
            ->middleware('check.permission:products.delete,products.manage');

        // Commission Management
        Route::get('/commission/rules', [AdminCommissionController::class, 'rules'])->name('commission.rules');
        Route::post('/commission/rules', [AdminCommissionController::class, 'storeRule'])->name('commission.rules.store');
        Route::put('/commission/rules/{rule}', [AdminCommissionController::class, 'updateRule'])->name('commission.rules.update');
        Route::delete('/commission/rules/{rule}', [AdminCommissionController::class, 'destroyRule'])->name('commission.rules.destroy');
        Route::get('/commission/records', [AdminCommissionController::class, 'records'])->name('commission.records');
        Route::post('/commission/process', [AdminCommissionController::class, 'processOrderCommission'])->name('commission.process');
        Route::get('/commission/settlements', [AdminCommissionController::class, 'settlements'])->name('commission.settlements');
        Route::patch('/commission/settlements/{settlement}/complete', [AdminCommissionController::class, 'completeSettlement'])->name('commission.settlements.complete');
        Route::post('/commission/settlements', [AdminCommissionController::class, 'createSettlement'])->name('commission.settlements.store');
        Route::get('/commission/seller-wallet', [AdminCommissionController::class, 'sellerWalletInfo'])->name('commission.seller-wallet');

        // Seller Payments
        Route::get('/seller-payments', [AdminSellerPaymentController::class, 'index'])->name('seller-payments.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/seller-payments/{order}', [AdminSellerPaymentController::class, 'show'])->name('seller-payments.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::post('/seller-payments/settlement', [AdminSellerPaymentController::class, 'storeSettlement'])->name('seller-payments.settlement.store')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/seller-payments/settlement/{settlement}/complete', [AdminSellerPaymentController::class, 'completeSettlement'])->name('seller-payments.settlement.complete')
            ->middleware('check.permission:vendors.manage');
        Route::get('/seller-payments/seller/{seller}/finance', [AdminSellerPaymentController::class, 'sellerFinance'])->name('seller-payments.seller-finance')
            ->middleware('check.permission:vendors.view,vendors.manage');

        // Courier Collections & Reconciliation
        Route::get('/courier-collections', [AdminCourierController::class, 'index'])->name('courier-collections.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/courier-collections/{collection}', [AdminCourierController::class, 'show'])->name('courier-collections.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::patch('/courier-collections/{collection}/confirm', [AdminCourierController::class, 'confirm'])->name('courier-collections.confirm')
            ->middleware('check.permission:vendors.manage');
        Route::post('/courier-collections/confirm-bulk', [AdminCourierController::class, 'confirmBulk'])->name('courier-collections.confirm-bulk')
            ->middleware('check.permission:vendors.manage');
        Route::post('/courier-collections/sync', [AdminCourierController::class, 'sync'])->name('courier-collections.sync')
            ->middleware('check.permission:vendors.manage');
        Route::post('/courier-collections/{collection}/sync-single', [AdminCourierController::class, 'syncSingle'])->name('courier-collections.sync-single')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/courier-collections/{collection}/reconcile', [AdminCourierController::class, 'reconcile'])->name('courier-collections.reconcile')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/courier-collections/{collection}/settlement-status', [AdminCourierController::class, 'updateSettlementStatus'])->name('courier-collections.update-settlement-status')
            ->middleware('check.permission:vendors.manage');

        // Courier Management
        Route::get('/couriers', [AdminCourierManagementController::class, 'index'])->name('couriers.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/couriers/create', [AdminCourierManagementController::class, 'create'])->name('couriers.create')
            ->middleware('check.permission:vendors.manage');
        Route::post('/couriers', [AdminCourierManagementController::class, 'store'])->name('couriers.store')
            ->middleware('check.permission:vendors.manage');
        Route::get('/couriers/{courier}', [AdminCourierManagementController::class, 'show'])->name('couriers.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/couriers/{courier}/edit', [AdminCourierManagementController::class, 'edit'])->name('couriers.edit')
            ->middleware('check.permission:vendors.manage');
        Route::put('/couriers/{courier}', [AdminCourierManagementController::class, 'update'])->name('couriers.update')
            ->middleware('check.permission:vendors.manage');
        Route::delete('/couriers/{courier}', [AdminCourierManagementController::class, 'destroy'])->name('couriers.destroy')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/couriers/{courier}/toggle-status', [AdminCourierManagementController::class, 'toggleStatus'])->name('couriers.toggle-status')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/couriers/{courier}/set-default', [AdminCourierManagementController::class, 'setDefault'])->name('couriers.set-default')
            ->middleware('check.permission:vendors.manage');
        Route::post('/couriers/{courier}/test-connection', [AdminCourierManagementController::class, 'testConnection'])->name('couriers.test-connection')
            ->middleware('check.permission:vendors.manage');

        // Courier API Settings
        Route::get('/courier-api-settings', [AdminCourierManagementController::class, 'apiSettings'])->name('couriers.api-settings')
            ->middleware('check.permission:vendors.manage');
        Route::put('/courier-api-settings/{courier}', [AdminCourierManagementController::class, 'updateApiSettings'])->name('couriers.api-settings.update')
            ->middleware('check.permission:vendors.manage');

        // Webhook Management
        Route::get('/courier-webhooks', [\App\Http\Controllers\Admin\AdminWebhookController::class, 'index'])->name('courier-webhooks.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/courier-webhooks/{log}', [\App\Http\Controllers\Admin\AdminWebhookController::class, 'show'])->name('courier-webhooks.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::post('/courier-webhooks/{log}/retry', [\App\Http\Controllers\Admin\AdminWebhookController::class, 'retry'])->name('courier-webhooks.retry')
            ->middleware('check.permission:vendors.manage');
        Route::delete('/courier-webhooks/{log}', [\App\Http\Controllers\Admin\AdminWebhookController::class, 'destroy'])->name('courier-webhooks.destroy')
            ->middleware('check.permission:vendors.manage');

        // Shipments
        Route::get('/shipments', [AdminShipmentController::class, 'index'])->name('shipments.index')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/shipments/create/{order}', [AdminShipmentController::class, 'create'])->name('shipments.create')
            ->middleware('check.permission:vendors.manage');
        Route::post('/shipments', [AdminShipmentController::class, 'store'])->name('shipments.store')
            ->middleware('check.permission:vendors.manage');
        Route::get('/shipments/{shipment}', [AdminShipmentController::class, 'show'])->name('shipments.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::post('/shipments/{shipment}/retry', [AdminShipmentController::class, 'retry'])->name('shipments.retry')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/shipments/{shipment}/sync-status', [AdminShipmentController::class, 'syncStatus'])->name('shipments.sync-status')
            ->middleware('check.permission:vendors.manage');
        Route::patch('/shipments/{shipment}/update-status', [AdminShipmentController::class, 'updateStatus'])->name('shipments.update-status')
            ->middleware('check.permission:vendors.manage');
        Route::get('/delivery-status', [AdminShipmentController::class, 'deliveryStatus'])->name('shipments.delivery-status')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::get('/tracking', [AdminShipmentController::class, 'tracking'])->name('shipments.tracking')
            ->middleware('check.permission:vendors.view,vendors.manage');
    });

// Seller Dashboard
Route::prefix('seller')
    ->name('seller.')
    ->middleware(['auth', 'ensure.active'])
    ->group(function () {
        // Seller registration - accessible to any authenticated user
        Route::get('/register', [SellerController::class, 'showRegisterForm'])->name('register.show');
        Route::post('/register', [SellerController::class, 'register'])->name('register.store');

        // Seller dashboard and management - restricted to seller/seller-staff roles
        Route::middleware('check.role:seller,seller-staff')->group(function () {
            Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
            Route::get('/analytics', [SellerDashboardController::class, 'index'])->name('analytics');
            Route::get('/profile/edit', [SellerController::class, 'editProfile'])->name('profile.edit');
            Route::put('/profile', [SellerController::class, 'updateProfile'])->name('profile.update');

            // Staff Management
            Route::get('/staff', [SellerStaffController::class, 'index'])->name('staff.index');
            Route::get('/staff/create', [SellerStaffController::class, 'create'])->name('staff.create');
            Route::post('/staff', [SellerStaffController::class, 'store'])->name('staff.store');
            Route::get('/staff/{staff}', [SellerStaffController::class, 'show'])->name('staff.show');
            Route::get('/staff/{staff}/edit', [SellerStaffController::class, 'edit'])->name('staff.edit');
            Route::put('/staff/{staff}', [SellerStaffController::class, 'update'])->name('staff.update');
            Route::post('/staff/{staff}/toggle-status', [SellerStaffController::class, 'toggleStatus'])->name('staff.toggle-status');
            Route::delete('/staff/{staff}', [SellerStaffController::class, 'destroy'])->name('staff.destroy');

            // Products
            Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
            Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}', [SellerProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
            Route::post('/products/{product}/submit', [SellerProductController::class, 'submitForReview'])->name('products.submit');
            Route::post('/products/{product}/duplicate', [SellerProductController::class, 'duplicate'])->name('products.duplicate');
            Route::post('/products/{product}/publish', [SellerProductController::class, 'publish'])->name('products.publish');
            Route::post('/products/{product}/unpublish', [SellerProductController::class, 'unpublish'])->name('products.unpublish');
            Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');

            // Category Attributes AJAX
            Route::get('/category-attributes/{category}', [SellerProductController::class, 'getCategoryAttributes'])->name('category-attributes.get');

            // Inventory
            Route::get('/inventory', [SellerInventoryController::class, 'index'])->name('inventory.index');
            Route::get('/inventory/{product}', [SellerInventoryController::class, 'show'])->name('inventory.show');
            Route::post('/inventory/{product}/adjust', [SellerInventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::post('/inventory/{product}/add-stock', [SellerInventoryController::class, 'addStock'])->name('inventory.add-stock');
            Route::post('/inventory/{product}/remove-stock', [SellerInventoryController::class, 'removeStock'])->name('inventory.remove-stock');
            Route::post('/inventory/{product}/generate-barcode', [SellerInventoryController::class, 'generateBarcode'])->name('inventory.generate-barcode');
            Route::post('/inventory/{product}/generate-qr', [SellerInventoryController::class, 'generateQrCode'])->name('inventory.generate-qr');
            Route::get('/inventory/{product}/print', [SellerInventoryController::class, 'printIdentifiers'])->name('inventory.print');

            // Orders
            Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::patch('/orders/{order}/note', [SellerOrderController::class, 'addNote'])->name('orders.note');
            Route::patch('/orders/{order}/tracking', [SellerOrderController::class, 'updateTracking'])->name('orders.tracking');
            Route::post('/orders/bulk-update', [SellerOrderController::class, 'bulkUpdate'])->name('orders.bulk-update');

            // Shipment Tracking (Seller)
            Route::get('/shipments', [\App\Http\Controllers\Seller\SellerShipmentController::class, 'index'])->name('shipments.index');
            Route::get('/shipments/{shipment}', [\App\Http\Controllers\Seller\SellerShipmentController::class, 'show'])->name('shipments.show');

            // Reviews (placeholder)
            Route::get('/reviews', function () {
                return redirect()->route('seller.dashboard')->with('info', 'Reviews management coming soon.');
            })->name('reviews.index');

            // Customers (placeholder)
            Route::get('/customers', function () {
                return redirect()->route('seller.dashboard')->with('info', 'Customer management coming soon.');
            })->name('customers.index');

            // Sales & Earnings (placeholder)
            Route::get('/sales', function () {
                return redirect()->route('seller.dashboard')->with('info', 'Sales & earnings coming soon.');
            })->name('sales.index');

            // Commission
            Route::get('/commission', [SellerCommissionController::class, 'index'])->name('commission.index');
            Route::get('/earnings', [SellerCommissionController::class, 'earnings'])->name('commission.earnings');

            // Wallet
            Route::get('/wallet', [SellerWalletController::class, 'index'])->name('wallet.index');
            Route::get('/withdrawals', [SellerSettlementController::class, 'index'])->name('withdrawals.index');

            // Notifications (placeholder)
            Route::get('/notifications', function () {
                return redirect()->route('seller.dashboard')->with('info', 'Notifications center coming soon.');
            })->name('notifications.index');

            // Warehouses
            Route::get('/warehouses', [SellerWarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('/warehouses/create', [SellerWarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('/warehouses', [SellerWarehouseController::class, 'store'])->name('warehouses.store');
            Route::get('/warehouses/{warehouse}', [SellerWarehouseController::class, 'show'])->name('warehouses.show');
            Route::get('/warehouses/{warehouse}/edit', [SellerWarehouseController::class, 'edit'])->name('warehouses.edit');
            Route::put('/warehouses/{warehouse}', [SellerWarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('/warehouses/{warehouse}', [SellerWarehouseController::class, 'destroy'])->name('warehouses.destroy');
        });
    });
