<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'roles'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);
        $superAdminRole->permissions()->sync(Permission::pluck('id'));

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);
        $adminRole->permissions()->sync(
            Permission::whereIn('slug', ['users.view', 'users.manage'])->pluck('id')
        );

        $customerRole = Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'is_system' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->superAdmin->roles()->attach($superAdminRole);

        $this->admin = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->customer->roles()->attach($customerRole);
    }

    private function getTokenForUser(User $user): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $user->apiTokens()->create([
            'token' => hash('sha256', $plainToken),
            'name' => 'test-token',
            'abilities' => ['*'],
            'expires_at' => now()->addDays(30),
        ]);
        return $plainToken;
    }

    public function test_user_has_role(): void
    {
        $this->assertTrue($this->superAdmin->hasRole('super-admin'));
        $this->assertTrue($this->admin->hasRole('admin'));
        $this->assertTrue($this->customer->hasRole('customer'));
        $this->assertFalse($this->customer->hasRole('admin'));
    }

    public function test_user_has_any_role(): void
    {
        $this->assertTrue($this->superAdmin->hasAnyRole(['super-admin', 'admin']));
        $this->assertFalse($this->customer->hasAnyRole(['super-admin', 'admin']));
    }

    public function test_user_has_permission(): void
    {
        $this->assertTrue($this->superAdmin->hasPermission('users.manage'));
        $this->assertTrue($this->admin->hasPermission('users.manage'));
        $this->assertFalse($this->customer->hasPermission('users.manage'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $this->assertTrue($this->superAdmin->hasPermission('anything'));
        $this->assertTrue($this->superAdmin->hasAnyPermission(['anything', 'something']));
    }

    public function test_user_has_any_permission(): void
    {
        $this->assertTrue($this->admin->hasAnyPermission(['users.view', 'users.manage']));
        $this->assertFalse($this->customer->hasAnyPermission(['users.view', 'users.manage']));
    }

    public function test_api_index_roles_super_admin(): void
    {
        $token = $this->getTokenForUser($this->superAdmin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/admin/roles');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_api_index_roles_unauthorized(): void
    {
        $token = $this->getTokenForUser($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/admin/roles');

        $response->assertStatus(403);
    }

    public function test_api_create_role(): void
    {
        $token = $this->getTokenForUser($this->superAdmin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/admin/roles', [
            'name' => 'Vendor Manager',
            'slug' => 'vendor-manager',
            'description' => 'Manages vendors',
            'permission_ids' => Permission::pluck('id')->toArray(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'slug']]);

        $this->assertDatabaseHas('roles', ['slug' => 'vendor-manager']);
    }

    public function test_api_update_role(): void
    {
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role', 'is_system' => false]);
        $token = $this->getTokenForUser($this->superAdmin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/admin/roles/{$role->id}", [
            'description' => 'Updated description',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_cannot_modify_system_role(): void
    {
        $role = Role::where('slug', 'super-admin')->first();
        $token = $this->getTokenForUser($this->superAdmin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/admin/roles/{$role->id}", [
            'description' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_api_assign_role(): void
    {
        $token = $this->getTokenForUser($this->superAdmin);
        $newUser = User::factory()->create();
        $role = Role::where('slug', 'customer')->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/admin/assign-role', [
            'user_id' => $newUser->id,
            'role_id' => $role->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('role_user', [
            'user_id' => $newUser->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_api_remove_role(): void
    {
        $token = $this->getTokenForUser($this->superAdmin);
        $role = Role::where('slug', 'customer')->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/admin/remove-role', [
            'user_id' => $this->customer->id,
            'role_id' => $role->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('role_user', [
            'user_id' => $this->customer->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_api_index_permissions(): void
    {
        $token = $this->getTokenForUser($this->superAdmin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/admin/permissions');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_user_model_relationships(): void
    {
        $this->assertNotEmpty($this->superAdmin->roles);
        $this->assertNotEmpty($this->superAdmin->roles->first()->permissions);
    }

    public function test_role_scope_active(): void
    {
        Role::create(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]);

        $activeRoles = Role::active()->get();

        $this->assertTrue($activeRoles->every(fn ($r) => $r->is_active === true));
    }

    public function test_system_setting_get_set(): void
    {
        SystemSetting::set('test_key', 'test_value');
        $value = SystemSetting::get('test_key');

        $this->assertEquals('test_value', $value);
    }
}
