<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder that runs inside tenant context.
 * Called by: php artisan tenants:seed --class=TenantSeeder --tenants=ID
 *
 * Expects tenant data to be available via tenant() helper.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = tenant();

        // Create admin user from tenant data
        \App\Models\User::create([
            'name' => $tenant->name,
            'email' => $tenant->email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Set store identity from tenant data
        \App\Models\Setting::set('store_name', $tenant->store_name);
        \App\Models\Setting::set('store_email', $tenant->email);

        // Run the standard seeders
        $this->call([
            CategorySeeder::class,
            CustomerSeeder::class,
            SettingSeeder::class,
            ExpenseSeeder::class,
            IncomeSeeder::class,
            CouponSeeder::class,
            CapacityLimitSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            RecipeSeeder::class,
            ReviewSeeder::class,
            WaitlistEntrySeeder::class,
        ]);
    }
}
