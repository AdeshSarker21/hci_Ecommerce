<?php

use App\Http\Controllers\Admin\AdminController;
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
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerInventoryController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerStaffController;
use App\Http\Controllers\Seller\SellerWarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [WebAuthController::class, 'login'])->middleware(['guest', 'throttle.login']);
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [WebAuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Public Product Search
Route::get('/search', [ProductSearchController::class, 'index'])->name('search');
Route::get('/product/{slug}', [ProductSearchController::class, 'show'])->name('product.show');

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
        Route::get('/sellers/{seller}', [AdminSellerController::class, 'show'])->name('sellers.show')
            ->middleware('check.permission:vendors.view,vendors.manage');
        Route::post('/sellers/{seller}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve')
            ->middleware('check.permission:vendors.manage');
        Route::post('/sellers/{seller}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject')
            ->middleware('check.permission:vendors.manage');
        Route::post('/sellers/{seller}/suspend', [AdminSellerController::class, 'suspend'])->name('sellers.suspend')
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
    });

// Seller Dashboard
Route::prefix('seller')
    ->name('seller.')
    ->middleware(['auth', 'ensure.active'])
    ->group(function () {
        Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
        Route::get('/register', [SellerController::class, 'showRegisterForm'])->name('register.show');
        Route::post('/register', [SellerController::class, 'register'])->name('register.store');
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

        // Warehouses
        Route::get('/warehouses', [SellerWarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/create', [SellerWarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [SellerWarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}', [SellerWarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('/warehouses/{warehouse}/edit', [SellerWarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{warehouse}', [SellerWarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [SellerWarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });
