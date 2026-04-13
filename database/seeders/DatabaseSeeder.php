<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Setting;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default super admin
        Staff::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        // Default currency
        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'prefix' => '$',
            'suffix' => null,
            'decimal_places' => 2,
            'exchange_rate' => 1.000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'prefix' => '€',
            'suffix' => null,
            'decimal_places' => 2,
            'exchange_rate' => 0.920000,
            'is_default' => false,
            'is_active' => true,
        ]);

        // Default settings
        $companyDefaults = [
            'company_name' => 'My Hosting Company',
            'company_email' => 'support@example.com',
            'company_address' => '',
            'company_city' => '',
            'company_state' => '',
            'company_postcode' => '',
            'company_country' => 'US',
            'company_phone' => '',
            'company_tax_id' => '',
            'company_website' => '',
        ];

        foreach ($companyDefaults as $key => $value) {
            Setting::set($key, $value, 'company');
        }

        $brandingDefaults = [
            'brand_name' => 'Client Portal',
            'brand_primary_color' => '#4f46e5',
        ];

        foreach ($brandingDefaults as $key => $value) {
            Setting::set($key, $value, 'branding');
        }
    }
}
