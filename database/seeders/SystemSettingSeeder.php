<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'Ecommerce', 'type' => 'string', 'group' => 'general', 'description' => 'Website name'],
            ['key' => 'site_description', 'value' => 'Multi-vendor eCommerce Marketplace', 'type' => 'string', 'group' => 'general', 'description' => 'Website description'],
            ['key' => 'site_url', 'value' => 'http://localhost:8000', 'type' => 'string', 'group' => 'general', 'description' => 'Website URL'],
            ['key' => 'site_logo', 'value' => null, 'type' => 'string', 'group' => 'general', 'description' => 'Site logo URL'],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'string', 'group' => 'general', 'description' => 'Favicon URL'],

            // Registration
            ['key' => 'allow_registration', 'value' => 'true', 'type' => 'boolean', 'group' => 'registration', 'description' => 'Allow public registration'],
            ['key' => 'default_user_role', 'value' => 'customer', 'type' => 'string', 'group' => 'registration', 'description' => 'Default role for new users'],
            ['key' => 'require_email_verification', 'value' => 'true', 'type' => 'boolean', 'group' => 'registration', 'description' => 'Require email verification'],

            // Currency
            ['key' => 'currency_code', 'value' => 'USD', 'type' => 'string', 'group' => 'commerce', 'description' => 'Default currency code'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'commerce', 'description' => 'Default currency symbol'],

            // Vendor
            ['key' => 'allow_vendor_registration', 'value' => 'true', 'type' => 'boolean', 'group' => 'vendor', 'description' => 'Allow vendor/seller registration'],
            ['key' => 'vendor_commission_rate', 'value' => '10', 'type' => 'float', 'group' => 'vendor', 'description' => 'Default commission rate percentage'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
