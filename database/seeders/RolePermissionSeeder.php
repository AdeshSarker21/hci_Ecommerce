<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'users'],
            ['name' => 'Update Users', 'slug' => 'users.update', 'group' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'users'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group' => 'roles'],
            ['name' => 'Update Roles', 'slug' => 'roles.update', 'group' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group' => 'roles'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'roles'],

            // Permissions
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'group' => 'permissions'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'group' => 'permissions'],
            ['name' => 'Update Permissions', 'slug' => 'permissions.update', 'group' => 'permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'group' => 'permissions'],

            // System Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'settings'],

            // Products (placeholder for future)
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'group' => 'products'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'group' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'group' => 'products'],
            ['name' => 'Manage Products', 'slug' => 'products.manage', 'group' => 'products'],

            // Orders (placeholder for future)
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'orders'],
            ['name' => 'Manage Orders', 'slug' => 'orders.manage', 'group' => 'orders'],

            // Vendors (placeholder for future)
            ['name' => 'View Vendors', 'slug' => 'vendors.view', 'group' => 'vendors'],
            ['name' => 'Manage Vendors', 'slug' => 'vendors.manage', 'group' => 'vendors'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions.',
                'is_system' => true,
                'sort_order' => 1,
                'permissions' => '*',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'System administrator with broad access.',
                'is_system' => true,
                'sort_order' => 2,
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.manage',
                    'roles.view', 'roles.create', 'roles.update',
                    'permissions.view',
                    'settings.view', 'settings.manage',
                    'products.view', 'products.create', 'products.update', 'products.manage',
                    'orders.view', 'orders.manage',
                    'vendors.view', 'vendors.manage',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Store manager with operational access.',
                'is_system' => true,
                'sort_order' => 3,
                'permissions' => [
                    'users.view',
                    'products.view', 'products.create', 'products.update',
                    'orders.view', 'orders.manage',
                    'vendors.view',
                ],
            ],
            [
                'name' => 'Product Manager',
                'slug' => 'product-manager',
                'description' => 'Manages product catalog and inventory.',
                'is_system' => true,
                'sort_order' => 4,
                'permissions' => [
                    'products.view', 'products.create', 'products.update', 'products.delete', 'products.manage',
                ],
            ],
            [
                'name' => 'Seller',
                'slug' => 'seller',
                'description' => 'Vendor who can list and sell products.',
                'is_system' => true,
                'sort_order' => 5,
                'permissions' => [
                    'products.view', 'products.create', 'products.update', 'products.delete',
                    'orders.view',
                ],
            ],
            [
                'name' => 'Seller Staff',
                'slug' => 'seller-staff',
                'description' => 'Staff member working for a seller.',
                'is_system' => true,
                'sort_order' => 6,
                'permissions' => [
                    'products.view', 'products.create', 'products.update',
                    'orders.view',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Regular customer who can browse and purchase.',
                'is_system' => true,
                'sort_order' => 7,
                'permissions' => [],
            ],
            [
                'name' => 'Delivery Staff',
                'slug' => 'delivery-staff',
                'description' => 'Handles delivery and support operations.',
                'is_system' => true,
                'sort_order' => 8,
                'permissions' => [
                    'orders.view', 'orders.manage',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            if ($permissions === '*') {
                $role->permissions()->sync(Permission::pluck('id'));
            } elseif (!empty($permissions)) {
                $role->permissions()->sync(
                    Permission::whereIn('slug', $permissions)->pluck('id')
                );
            } else {
                $role->permissions()->detach();
            }
        }
    }
}
