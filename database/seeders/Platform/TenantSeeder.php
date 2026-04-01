<?php

namespace Database\Seeders\Platform;

use App\Models\Staff\User;
use Database\Seeders\Content\BlogPostSeeder;
use Database\Seeders\Customers\CustomerSeeder;
use Database\Seeders\Customers\ReviewSeeder;
use Database\Seeders\Customers\WaitlistEntrySeeder;
use Database\Seeders\Financial\CouponSeeder;
use Database\Seeders\Financial\ExpenseSeeder;
use Database\Seeders\Financial\IncomeSeeder;
use Database\Seeders\Inventory\CategorySeeder;
use Database\Seeders\Inventory\ProductSeeder;
use Database\Seeders\Inventory\RecipeSeeder;
use Database\Seeders\Operations\BusinessScheduleSeeder;
use Database\Seeders\Operations\CapacityLimitSeeder;
use Database\Seeders\Operations\SettingSeeder;
use Database\Seeders\Orders\OrderSeeder;
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
        User::query()->firstOrCreate(['email' => $tenant->email], [
            'name' => $tenant->name,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Set store identity from tenant data
        settings(['store_name' => $tenant->store_name]);
        settings(['store_email' => $tenant->email]);

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
            BusinessScheduleSeeder::class,
            BlogPostSeeder::class,
        ]);
    }
}
