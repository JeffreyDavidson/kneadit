<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyStorefrontTest extends TestCase
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
    }

    public function test_loyalty_point_model_exists(): void
    {
        $this->assertTrue(class_exists(LoyaltyPoint::class));
    }

    public function test_loyalty_reward_model_exists(): void
    {
        $this->assertTrue(class_exists(LoyaltyReward::class));
    }

    public function test_customer_total_points_calculation(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'earned',
            'description' => 'Order #1',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 50,
            'type' => 'earned',
            'description' => 'Order #2',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 30,
            'type' => 'redeemed',
            'description' => 'Reward redeemed',
        ]);

        $this->assertEquals(120, $customer->total_points);
    }

    public function test_loyalty_reward_can_be_created(): void
    {
        $reward = LoyaltyReward::create([
            'name' => 'Free Cookie',
            'description' => 'Get a free cookie!',
            'points_required' => 100,
            'reward_type' => 'free_product',
            'reward_value' => 0,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('loyalty_rewards', ['name' => 'Free Cookie']);
        $this->assertTrue($reward->is_active);
    }

    public function test_loyalty_reward_type_label_percentage(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '10% Off',
            'points_required' => 50,
            'reward_type' => 'percentage_discount',
            'reward_value' => 10,
            'is_active' => true,
        ]);

        $this->assertStringContainsString('% Off', $reward->reward_type_label);
    }

    public function test_loyalty_reward_type_label_fixed(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '$5 Off',
            'points_required' => 75,
            'reward_type' => 'fixed_discount',
            'reward_value' => 5.00,
            'is_active' => true,
        ]);

        $this->assertEquals('$5.00 Off', $reward->reward_type_label);
    }

    public function test_customer_lifetime_points_earned(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'lifetime@example.com',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 200,
            'type' => 'earned',
            'description' => 'Big order',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 50,
            'type' => 'redeemed',
            'description' => 'Reward',
        ]);

        $this->assertEquals(200, $customer->lifetime_points_earned);
    }

    public function test_loyalty_points_belong_to_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'belong@example.com',
        ]);

        $point = LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'earned',
            'description' => 'Order',
        ]);

        $this->assertEquals($customer->id, $point->customer->id);
    }
}
