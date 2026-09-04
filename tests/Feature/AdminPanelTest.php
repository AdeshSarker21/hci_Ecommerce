<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $manager;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'roles'],
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'group' => 'permissions'],
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products'],
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'orders'],
            ['name' => 'View Vendors', 'slug' => 'vendors.view', 'group' => 'vendors'],
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // Super Admin role - all permissions
        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'is_system' => true]);
        $superAdminRole->permissions()->sync(Permission::pluck('id'));

        // Admin role - users and roles
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'is_system' => true]);
        $adminRole->permissions()->sync(
            Permission::whereIn('slug', ['users.view', 'users.manage', 'roles.view'])->pluck('id')
        );

        // Manager role - limited
        $managerRole = Role::create(['name' => 'Manager', 'slug' => 'manager', 'is_system' => true]);
        $managerRole->permissions()->sync(
            Permission::whereIn('slug', ['users.view', 'products.view', 'orders.view'])->pluck('id')
        );

        // Customer role - no admin permissions
        $customerRole = Role::create(['name' => 'Customer', 'slug' => 'customer', 'is_system' => true]);

        $this->superAdmin = User::factory()->create(['status' => 'active', 'is_active' => true]);
        $this->superAdmin->roles()->attach($superAdminRole);

        $this->admin = User::factory()->create(['status' => 'active', 'is_active' => true]);
        $this->admin->roles()->attach($adminRole);

        $this->manager = User::factory()->create(['status' => 'active', 'is_active' => true]);
        $this->manager->roles()->attach($managerRole);

        $this->customer = User::factory()->create(['status' => 'active', 'is_active' => true]);
        $this->customer->roles()->attach($customerRole);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_manager_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_super_admin_can_view_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertViewIs('admin.users.index');
    }

    public function test_admin_can_view_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertOk();
    }

    public function test_manager_can_view_users(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.users.index'));
        $response->assertOk();
    }

    public function test_customer_cannot_view_users(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));
        $response->assertOk();
        $response->assertViewIs('admin.roles.index');
    }

    public function test_admin_can_view_roles(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));
        $response->assertOk();
    }

    public function test_manager_cannot_view_roles(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.roles.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.permissions.index'));
        $response->assertOk();
        $response->assertViewIs('admin.permissions.index');
    }

    public function test_admin_cannot_view_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index'));
        $response->assertStatus(403);
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Total Users');
        $response->assertSee('Active Users');
        $response->assertSee('Roles');
        $response->assertSee('Permissions');
    }

    public function test_admin_users_page_shows_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee($this->admin->name);
        $response->assertSee($this->admin->email);
    }

    public function test_admin_user_show_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.show', $this->admin));
        $response->assertOk();
        $response->assertSee($this->admin->name);
        $response->assertSee($this->admin->email);
    }

    public function test_admin_roles_page_shows_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));
        $response->assertOk();
        $response->assertSee('Super Admin');
        $response->assertSee('Admin');
        $response->assertSee('Customer');
    }

    public function test_admin_role_show_page(): void
    {
        $role = Role::where('slug', 'super-admin')->first();
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.show', $role));
        $response->assertOk();
        $response->assertSee('Super Admin');
    }

    public function test_admin_permissions_page_shows_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.permissions.index'));
        $response->assertOk();
        $response->assertSee('users');
        $response->assertSee('roles');
    }

    public function test_dashboard_has_admin_link(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Admin Panel');
    }

    public function test_inactive_user_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_active' => false, 'status' => 'inactive']);
        $user->roles()->attach(Role::where('slug', 'super-admin')->first());

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }
}
