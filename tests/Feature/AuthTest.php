<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleSlug = 'customer'): User
    {
        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucfirst($roleSlug), 'is_system' => true]
        );

        $user = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($role);

        return $user;
    }

    private function createApiToken(User $user): string
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

    public function test_api_register_success(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_api_register_validation_error(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_api_register_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_login_success(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);
    }

    public function test_api_login_invalid_credentials(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_api_login_inactive_user(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_api_me_authenticated(): void
    {
        $user = $this->createUserWithRole();
        $token = $this->createApiToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'email'],
            ]);
    }

    public function test_api_me_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_api_logout(): void
    {
        $user = $this->createUserWithRole();
        $token = $this->createApiToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_api_update_profile(): void
    {
        $user = $this->createUserWithRole();
        $token = $this->createApiToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'phone' => '+1234567890',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+1234567890',
        ]);
    }

    public function test_api_change_password(): void
    {
        $user = $this->createUserWithRole();
        $token = $this->createApiToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_web_login_success(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_web_login_failure(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_web_register_success(): void
    {
        $response = $this->post('/register', [
            'name' => 'Web User',
            'email' => 'web@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'web@example.com']);
    }

    public function test_web_logout(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
