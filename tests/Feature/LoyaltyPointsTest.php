<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoyaltyPointsTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        Mail::fake();
        $this->user = User::create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        Setting::set('loyalty_enabled', '1');
        Setting::set('loyalty_points_per_dollar', '10');
        $this->customer = Customer::create(['name' => 'Loyalty Tester', 'email' => 'loyal@test.com']);
    }

    /** @test */
    public function points_awarded_when_order_delivered(): void
    {
        $order = Order::create(['user_id' => $this->user->id, 
            'customer_id' => $this->customer->id,
            'status' => 'confirmed',
            'total' => 25.00,
            'subtotal' => 25.00,
        ]);

        $order->update(['status' => 'delivered']);

        $this->assertDatabaseHas('loyalty_points', [
            'customer_id' => $this->customer->id,
            'order_id' => $order->id,
            'type' => 'earned',
        ]);
    }

    /** @test */
    public function points_calculated_correctly(): void
    {
        $order = Order::create(['user_id' => $this->user->id, 
            'customer_id' => $this->customer->id,
            'status' => 'confirmed',
            'total' => 25.50,
            'subtotal' => 25.50,
        ]);

        $order->update(['status' => 'delivered']);

        $points = LoyaltyPoint::where('order_id', $order->id)->first();
        $this->assertEquals(255, $points->points); // 25.50 * 10
    }

    /** @test */
    public function points_not_awarded_when_loyalty_disabled(): void
    {
        Setting::set('loyalty_enabled', '0');

        $order = Order::create(['user_id' => $this->user->id, 
            'customer_id' => $this->customer->id,
            'status' => 'confirmed',
            'total' => 25.00,
            'subtotal' => 25.00,
        ]);

        $order->update(['status' => 'delivered']);

        $this->assertEquals(0, LoyaltyPoint::where('order_id', $order->id)->count());
    }

    /** @test */
    public function points_not_double_awarded(): void
    {
        $order = Order::create(['user_id' => $this->user->id, 
            'customer_id' => $this->customer->id,
            'status' => 'confirmed',
            'total' => 25.00,
            'subtotal' => 25.00,
        ]);

        $order->update(['status' => 'delivered']);
        // Simulate re-save
        $order->update(['status' => 'delivered']);

        $this->assertEquals(1, LoyaltyPoint::where('order_id', $order->id)->where('type', 'earned')->count());
    }

    /** @test */
    public function total_points_calculated_correctly(): void
    {
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 100, 'type' => 'earned', 'description' => 'test']);
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 50, 'type' => 'earned', 'description' => 'test']);
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 30, 'type' => 'redeemed', 'description' => 'test']);

        $this->assertEquals(120, $this->customer->total_points); // 100 + 50 - 30
    }

    /** @test */
    public function lifetime_points_only_counts_earned(): void
    {
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 100, 'type' => 'earned', 'description' => 'test']);
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 50, 'type' => 'earned', 'description' => 'test']);
        LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 30, 'type' => 'redeemed', 'description' => 'test']);

        $this->assertEquals(150, $this->customer->lifetime_points_earned);
    }
}
