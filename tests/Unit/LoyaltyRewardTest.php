<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRewardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    }

    /** @test */
    public function reward_type_label_for_percentage_discount(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '10% Off',
            'points_required' => 100,
            'reward_type' => 'percentage_discount',
            'reward_value' => 10.00,
            'is_active' => true,
        ]);

        $this->assertEquals('10.00% Off', $reward->reward_type_label);
    }

    /** @test */
    public function reward_type_label_for_fixed_discount(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '$5 Off',
            'points_required' => 50,
            'reward_type' => 'fixed_discount',
            'reward_value' => 5.00,
            'is_active' => true,
        ]);

        $this->assertEquals('$5.00 Off', $reward->reward_type_label);
    }

    /** @test */
    public function reward_type_label_for_free_product(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

        $reward = LoyaltyReward::create([
            'name' => 'Free Sourdough',
            'points_required' => 200,
            'reward_type' => 'free_product',
            'reward_value' => 0,
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertEquals('Free Sourdough', $reward->reward_type_label);
    }

    /** @test */
    public function reward_belongs_to_product(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

        $reward = LoyaltyReward::create([
            'name' => 'Free Sourdough',
            'points_required' => 200,
            'reward_type' => 'free_product',
            'reward_value' => 0,
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Product::class, $reward->product);
    }

    /** @test */
    public function is_active_is_cast_to_boolean(): void
    {
        $reward = LoyaltyReward::create([
            'name' => 'Reward',
            'points_required' => 100,
            'reward_type' => 'percentage_discount',
            'reward_value' => 10,
            'is_active' => true,
        ]);

        $this->assertIsBool($reward->is_active);
    }
}
