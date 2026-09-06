<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\SellerStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerSidebarTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'approved',
        ]);
    }

    private function actingAsApprovedSeller(): void
    {
        $this->actingAs($this->owner);
    }

    private function getSidebarHtml(string $url): string
    {
        $response = $this->get($url);
        $response->assertOk();
        $html = $response->getContent();

        // Extract just the sidebar <aside>...</aside>
        if (preg_match('/<aside[^>]*>(.*?)<\/aside>/s', $html, $matches)) {
            return $matches[1];
        }

        return $html;
    }

    // --- Sidebar renders for owner ---

    public function test_owner_sees_sidebar_on_dashboard(): void
    {
        $this->actingAsApprovedSeller();

        $response = $this->get(route('seller.dashboard'));
        $response->assertRedirect(route('seller.analytics'));
    }

    public function test_owner_sees_sidebar_on_analytics(): void
    {
        $this->actingAsApprovedSeller();

        $response = $this->get(route('seller.analytics'));
        $response->assertOk();
        $response->assertSee('Dashboard');
    }

    // --- All visible menu links return 200 ---

    public function test_owner_dashboard_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.dashboard'))->assertRedirect(route('seller.analytics'));
    }

    public function test_owner_analytics_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.analytics'))->assertOk();
    }

    public function test_owner_profile_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.profile.edit'))->assertOk();
    }

    public function test_owner_products_index_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.products.index'))->assertOk();
    }

    public function test_owner_products_create_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.products.create'))->assertOk();
    }

    public function test_owner_inventory_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.inventory.index'))->assertOk();
    }

    public function test_owner_orders_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.orders.index'))->assertOk();
    }

    public function test_owner_commission_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.commission.index'))->assertOk();
    }

    public function test_owner_earnings_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.commission.earnings'))->assertOk();
    }

    public function test_owner_wallet_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.wallet.index'))->assertOk();
    }

    public function test_owner_withdrawals_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.withdrawals.index'))->assertOk();
    }

    public function test_owner_staff_link_works(): void
    {
        $this->actingAsApprovedSeller();
        $this->get(route('seller.staff.index'))->assertOk();
    }

    // --- Sidebar structure for owner ---

    public function test_owner_sees_store_section(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('My Store', $sidebar);
        $this->assertStringContainsString('Store Settings', $sidebar);
    }

    public function test_owner_sees_products_section(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('Products', $sidebar);
        $this->assertStringContainsString('All Products', $sidebar);
        $this->assertStringContainsString('Add Product', $sidebar);
    }

    public function test_owner_sees_inventory(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('Inventory', $sidebar);
    }

    public function test_owner_sees_orders(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('All Orders', $sidebar);
    }

    public function test_owner_sees_finance_section(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('Finance', $sidebar);
        $this->assertStringContainsString('Commission', $sidebar);
        $this->assertStringContainsString('Earnings', $sidebar);
        $this->assertStringContainsString('Wallet', $sidebar);
        $this->assertStringContainsString('Withdrawals', $sidebar);
    }

    public function test_owner_sees_analytics_section(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('Analytics', $sidebar);
        $this->assertStringContainsString('Sales Analytics', $sidebar);
    }

    public function test_owner_sees_staff_management(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('Staff Management', $sidebar);
    }

    // --- Placeholder menus are hidden ---

    public function test_reviews_menu_is_hidden(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringNotContainsString('Product Reviews', $sidebar);
        $this->assertStringNotContainsString('Seller Reviews', $sidebar);
    }

    public function test_notifications_menu_is_hidden(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringNotContainsString('Notifications', $sidebar);
    }

    // --- No duplicate menus ---

    public function test_no_duplicate_store_settings_in_sidebar(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        preg_match_all('/Store Settings/', $sidebar, $matches);
        $this->assertCount(1, $matches[0], 'Store Settings should appear exactly once in sidebar');
    }

    public function test_no_duplicate_my_store_in_sidebar(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        preg_match_all('/My Store/', $sidebar, $matches);
        $this->assertCount(1, $matches[0], 'My Store should appear exactly once in sidebar');
    }

    // --- Collapsible menu structure ---

    public function test_sidebar_has_collapsible_structure(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('openProducts', $sidebar);
        $this->assertStringContainsString('openFinance', $sidebar);
        $this->assertStringContainsString('openAnalytics', $sidebar);
    }

    public function test_sidebar_has_alpine_collapse_directive(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        $this->assertStringContainsString('x-collapse', $sidebar);
    }

    // --- Active state highlighting ---

    public function test_active_state_on_products_page(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.products.index'));

        // Products collapsible should be open
        $this->assertStringContainsString('openProducts: true', $sidebar);
    }

    public function test_active_state_on_commission_page(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.commission.index'));

        // Finance collapsible should be open
        $this->assertStringContainsString('openFinance: true', $sidebar);
    }

    // --- Pending seller sees limited sidebar ---

    public function test_pending_seller_sees_only_dashboard_in_sidebar(): void
    {
        $pendingUser = User::factory()->create();
        Seller::factory()->create(['user_id' => $pendingUser->id, 'status' => 'pending']);

        $this->actingAs($pendingUser);
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        // Should only see Dashboard link
        $this->assertStringContainsString('Dashboard', $sidebar);
        // Should NOT see any menu sections (check for actual menu text, not x-data variable names)
        $this->assertStringNotContainsString('>Store<', $sidebar);
        $this->assertStringNotContainsString('>Products<', $sidebar);
        $this->assertStringNotContainsString('>Orders<', $sidebar);
        $this->assertStringNotContainsString('>Finance<', $sidebar);
        $this->assertStringNotContainsString('All Products', $sidebar);
        $this->assertStringNotContainsString('All Orders', $sidebar);
        $this->assertStringNotContainsString('Inventory', $sidebar);
    }

    // --- Mobile responsive ---

    public function test_sidebar_has_mobile_toggle(): void
    {
        $this->actingAsApprovedSeller();
        $response = $this->get(route('seller.dashboard'));
        $content = $response->getContent();

        $this->assertStringContainsString('sidebarOpen', $content);
        $this->assertStringContainsString('lg:translate-x-0', $content);
    }

    // --- Permission visibility (owner always sees everything) ---

    public function test_owner_always_has_full_sidebar(): void
    {
        $this->actingAsApprovedSeller();
        $sidebar = $this->getSidebarHtml(route('seller.dashboard'));

        // Owner should see all sections
        $this->assertStringContainsString('My Store', $sidebar);
        $this->assertStringContainsString('All Products', $sidebar);
        $this->assertStringContainsString('Inventory', $sidebar);
        $this->assertStringContainsString('All Orders', $sidebar);
        $this->assertStringContainsString('Finance', $sidebar);
        $this->assertStringContainsString('Analytics', $sidebar);
        $this->assertStringContainsString('Staff Management', $sidebar);
    }
}
