<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSellerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(['name' => 'super-admin', 'slug' => 'super-admin']);
        $this->superAdmin->roles()->attach($role);

        $this->seller = Seller::factory()->create([
            'status' => 'approved',
            'user_id' => User::factory()->create()->id,
        ]);
    }

    public function test_super_admin_can_view_sellers_index(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.index'));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.index');
    }

    public function test_super_admin_can_view_pending_approvals(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.pending'));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.pending');
    }

    public function test_super_admin_can_view_performance(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.performance'));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.performance');
    }

    public function test_super_admin_can_view_seller_details(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.show', $this->seller));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.show');
    }

    public function test_super_admin_can_view_seller_products(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.products', $this->seller));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.products');
    }

    public function test_super_admin_can_view_seller_orders(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.orders', $this->seller));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.orders');
    }

    public function test_super_admin_can_view_seller_finance(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.finance', $this->seller));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.finance');
    }

    public function test_super_admin_can_view_seller_activity(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.activity', $this->seller));
        $response->assertOk();
        $response->assertViewIs('admin.sellers.activity');
    }

    public function test_super_admin_can_approve_pending_seller(): void
    {
        $pendingSeller = Seller::factory()->create(['status' => 'pending', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.approve', $pendingSeller));
        $response->assertRedirect();
        $pendingSeller->refresh();
        $this->assertEquals('approved', $pendingSeller->status);
    }

    public function test_super_admin_can_reject_pending_seller_with_reason(): void
    {
        $pendingSeller = Seller::factory()->create(['status' => 'pending', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.reject', $pendingSeller), [
            'rejection_reason' => 'Incomplete documentation',
        ]);
        $response->assertRedirect();
        $pendingSeller->refresh();
        $this->assertEquals('rejected', $pendingSeller->status);
        $this->assertEquals('Incomplete documentation', $pendingSeller->rejection_reason);
    }

    public function test_rejection_requires_reason(): void
    {
        $pendingSeller = Seller::factory()->create(['status' => 'pending', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.reject', $pendingSeller), [
            'rejection_reason' => '',
        ]);
        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_super_admin_can_suspend_approved_seller(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.suspend', $this->seller), [
            'rejection_reason' => 'Policy violation',
        ]);
        $response->assertRedirect();
        $this->seller->refresh();
        $this->assertEquals('suspended', $this->seller->status);
    }

    public function test_super_admin_can_activate_suspended_seller(): void
    {
        $this->seller->update(['status' => 'suspended']);

        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.activate', $this->seller));
        $response->assertRedirect();
        $this->seller->refresh();
        $this->assertEquals('approved', $this->seller->status);
    }

    public function test_super_admin_can_activate_rejected_seller(): void
    {
        $this->seller->update(['status' => 'rejected', 'rejection_reason' => 'Old reason']);

        $this->actingAs($this->superAdmin);
        $response = $this->post(route('admin.sellers.activate', $this->seller));
        $response->assertRedirect();
        $this->seller->refresh();
        $this->assertEquals('approved', $this->seller->status);
        $this->assertNull($this->seller->rejection_reason);
    }

    public function test_activity_logged_on_approve(): void
    {
        $pendingSeller = Seller::factory()->create(['status' => 'pending', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->superAdmin);
        $this->post(route('admin.sellers.approve', $pendingSeller));

        $this->assertDatabaseHas('activity_logs', [
            'type' => 'seller.approved',
            'subject_type' => Seller::class,
            'subject_id' => $pendingSeller->id,
        ]);
    }

    public function test_activity_logged_on_reject(): void
    {
        $pendingSeller = Seller::factory()->create(['status' => 'pending', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->superAdmin);
        $this->post(route('admin.sellers.reject', $pendingSeller), [
            'rejection_reason' => 'Test reason',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'type' => 'seller.rejected',
            'subject_type' => Seller::class,
            'subject_id' => $pendingSeller->id,
        ]);
    }

    public function test_activity_logged_on_suspend(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('admin.sellers.suspend', $this->seller), [
            'rejection_reason' => 'Violation',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'type' => 'seller.suspended',
            'subject_type' => Seller::class,
            'subject_id' => $this->seller->id,
        ]);
    }

    public function test_activity_logged_on_activate(): void
    {
        $this->seller->update(['status' => 'suspended']);

        $this->actingAs($this->superAdmin);
        $this->post(route('admin.sellers.activate', $this->seller));

        $this->assertDatabaseHas('activity_logs', [
            'type' => 'seller.activated',
            'subject_type' => Seller::class,
            'subject_id' => $this->seller->id,
        ]);
    }

    public function test_sidebar_has_marketplace_section(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.index'));
        $content = $response->getContent();

        $this->assertStringContainsString('Marketplace', $content);
        $this->assertStringContainsString('All Sellers', $content);
        $this->assertStringContainsString('Seller Approvals', $content);
        $this->assertStringContainsString('Seller Performance', $content);
    }

    public function test_sidebar_has_seller_finance_section(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('admin.sellers.index'));
        $content = $response->getContent();

        $this->assertStringContainsString('Seller Finance', $content);
        $this->assertStringContainsString('Commissions', $content);
        $this->assertStringContainsString('Settlements', $content);
        $this->assertStringContainsString('Withdrawals', $content);
    }
}
