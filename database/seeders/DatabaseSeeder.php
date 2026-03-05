<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the default user first (required for orders)
        User::updateOrCreate(
            ['email' => 'baker@kneaditbakery.com'],
            [
                'name' => 'KneadIt Baker',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Seed all tables in dependency order
        $this->call([
            // Core data with no dependencies
            CategorySeeder::class,
            CustomerSeeder::class,
            SettingSeeder::class,
            ExpenseSeeder::class,
            IncomeSeeder::class,
            CouponSeeder::class,
            CapacityLimitSeeder::class,
            
            // Data that depends on categories
            ProductSeeder::class,
            
            // Data that depends on products and customers
            OrderSeeder::class,
            RecipeSeeder::class,
            ReviewSeeder::class,
            WaitlistEntrySeeder::class,
        ]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('🥖 Categories: 9 bakery categories');
        $this->command->info('🧁 Products: 35 artisan baked goods');
        $this->command->info('👥 Customers: 20 Florida customers');
        $this->command->info('📦 Orders: 65 orders with realistic data');
        $this->command->info('📝 Recipes: 15 detailed recipes with costs');
        $this->command->info('⭐ Reviews: 25 customer reviews');
        $this->command->info('💰 Expenses: 30 business expenses');
        $this->command->info('💵 Income: 15 income entries');
        $this->command->info('🎟️  Coupons: 5 promotional coupons');
        $this->command->info('⚙️  Settings: Configured store settings');
        $this->command->info('⏰ Waitlist: 5 waitlist entries');
        $this->command->info('📅 Capacity: Daily capacity limits set');
        $this->command->info('');
        $this->command->info('🎉 KneadIt Demo Bakery is ready to explore!');
    }
}