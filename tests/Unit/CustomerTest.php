<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    }

    /** @test */
    public function lifetime_value_sums_non_cancelled_orders(): void
    {
        $customer = Customer::create(['name' => 'Alice', 'email' => 'alice@test.com']);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 50.00, 'subtotal' => 50]);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'confirmed', 'total' => 30.00, 'subtotal' => 30]);

        $this->assertEquals(80.00, $customer->lifetime_value);
    }

    /** @test */
    public function lifetime_value_excludes_cancelled_orders(): void
    {
        $customer = Customer::create(['name' => 'Bob', 'email' => 'bob@test.com']);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 50.00, 'subtotal' => 50]);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'cancelled', 'total' => 30.00, 'subtotal' => 30]);

        $this->assertEquals(50.00, $customer->lifetime_value);
    }

    /** @test */
    public function order_count_counts_non_cancelled_orders(): void
    {
        $customer = Customer::create(['name' => 'Carol', 'email' => 'carol@test.com']);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 10, 'subtotal' => 10]);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'cancelled', 'total' => 20, 'subtotal' => 20]);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'ready', 'total' => 15, 'subtotal' => 15]);

        $this->assertEquals(2, $customer->order_count);
    }

    /** @test */
    public function average_order_value_is_correct(): void
    {
        $customer = Customer::create(['name' => 'Dave', 'email' => 'dave@test.com']);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 40, 'subtotal' => 40]);
        Order::create(['user_id' => $this->user->id, 'customer_id' => $customer->id, 'status' => 'delivered', 'total' => 60, 'subtotal' => 60]);

        $this->assertEquals(50.00, $customer->average_order_value);
    }

    /** @test */
    public function average_order_value_is_zero_with_no_orders(): void
    {
        $customer = Customer::create(['name' => 'Eve', 'email' => 'eve@test.com']);
        $this->assertEquals(0, $customer->average_order_value);
    }

    /** @test */
    public function days_since_last_order_returns_correct_number(): void
    {
        $customer = Customer::create(['name' => 'Frank', 'email' => 'frank@test.com']);
        $order = Order::create(['user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total' => 10,
            'subtotal' => 10,
        ]);
        Order::where('id', $order->id)->update(['created_at' => now()->subDays(15)]);

        $this->assertEquals(15, $customer->days_since_last_order);
    }

    /** @test */
    public function days_since_last_order_is_null_with_no_orders(): void
    {
        $customer = Customer::create(['name' => 'Grace', 'email' => 'grace@test.com']);
        $this->assertNull($customer->days_since_last_order);
    }

    /** @test */
    public function is_at_risk_when_30_plus_days_inactive(): void
    {
        $customer = Customer::create(['name' => 'Hank', 'email' => 'hank@test.com']);
        $order = Order::create(['user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total' => 10,
            'subtotal' => 10,
        ]);
        Order::where('id', $order->id)->update(['created_at' => now()->subDays(35)]);

        $this->assertTrue($customer->is_at_risk);
    }

    /** @test */
    public function is_not_at_risk_for_recent_customers(): void
    {
        $customer = Customer::create(['name' => 'Ivy', 'email' => 'ivy@test.com']);
        $order = Order::create(['user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total' => 10,
            'subtotal' => 10,
        ]);
        Order::where('id', $order->id)->update(['created_at' => now()->subDays(5)]);

        $this->assertFalse($customer->is_at_risk);
    }

    /** @test */
    public function is_not_at_risk_with_no_orders(): void
    {
        $customer = Customer::create(['name' => 'Jack', 'email' => 'jack@test.com']);
        $this->assertFalse($customer->is_at_risk);
    }

    /** @test */
    public function last_order_date_returns_most_recent(): void
    {
        $customer = Customer::create(['name' => 'Kate', 'email' => 'kate@test.com']);
        $order1 = Order::create(['user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total' => 10,
            'subtotal' => 10,
        ]);
        Order::where('id', $order1->id)->update(['created_at' => now()->subDays(10)]);
        $order2 = Order::create(['user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total' => 20,
            'subtotal' => 20,
        ]);
        Order::where('id', $order2->id)->update(['created_at' => now()->subDays(2)]);

        $this->assertEquals(now()->subDays(2)->toDateString(), $customer->last_order_date->toDateString());
    }
}
