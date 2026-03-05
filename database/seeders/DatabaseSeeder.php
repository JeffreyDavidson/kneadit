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
        // Use DB::table to avoid double-hashing (User model has 'password' => 'hashed' cast)
        \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
            ['email' => 'baker@kneaditbakery.com'],
            [
                'name' => 'KneadIt Baker',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
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

        if ($this->command) {
            $this->command->info('Demo data seeded successfully!');
        }
    }
}