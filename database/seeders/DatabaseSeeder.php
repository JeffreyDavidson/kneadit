<?php

namespace Database\Seeders;

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
use Database\Seeders\Platform\CentralSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // If running in a tenant context, seed tenant data
        // If running in central context (migrate:fresh --seed), only seed central data
        if (tenant()) {
            $this->seedTenantData();
        } else {
            $this->seedCentralData();
        }
    }

    /**
     * Seed central database (users, holidays, etc.)
     */
    protected function seedCentralData(): void
    {
        User::query()->updateOrCreate(['email' => 'jeffrey@getkneadit.app'], [
            'name' => 'Jeffrey Davidson',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'platform_admin',
        ]);

        $this->call([
            CentralSeeder::class,
            BlogPostSeeder::class,
        ]);

        if ($this->command) {
            $this->command->info('Central database seeded. Use `php artisan tenant:demo --fresh` to create a demo tenant with sample data.');
        }
    }

    /**
     * Seed tenant database (categories, products, orders, etc.)
     */
    protected function seedTenantData(): void
    {
        // Create the tenant admin user
        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => 'baker@kneaditbakery.com'],
            [
                'name' => 'KneadIt Baker',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        // Seed all tenant tables in dependency order
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

        if ($this->command) {
            $this->command->info('Tenant data seeded successfully!');
        }
    }
}
