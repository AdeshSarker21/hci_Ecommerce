<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Policies\AttributePolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\SellerPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        \App\Models\Role::class => RolePolicy::class,
        \App\Models\Permission::class => PermissionPolicy::class,
        Seller::class => SellerPolicy::class,
        Category::class => CategoryPolicy::class,
        Brand::class => BrandPolicy::class,
        Attribute::class => AttributePolicy::class,
        Product::class => ProductPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
