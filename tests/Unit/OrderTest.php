<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
        $this->customer = Customer::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    }

    protected function makeOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 10.00,
            'total' => 10.00,
            'requested_date' => now()->addDay(),
            'requested_time' => '10:00',
        ], $attrs));
    }

    /** @test */
    public function order_has_customer_relationship(): void
    {
        $order = $this->makeOrder();
        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals($this->customer->id, $order->customer->id);
    }

    /** @test */
    public function order_has_items_relationship(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);
        $order = $this->makeOrder();

        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 5.00]);

        $this->assertCount(1, $order->orderItems);
        $this->assertEquals(2, $order->orderItems->first()->quantity);
    }

    /** @test */
    public function order_has_messages_relationship(): void
    {
        $order = $this->makeOrder();
        OrderMessage::create(['order_id' => $order->id, 'message' => 'Hello', 'sender_type' => 'bakery', 'sender_name' => 'Baker']);
        $this->assertCount(1, $order->messages);
    }

    /** @test */
    public function order_number_is_auto_generated(): void
    {
        $order = $this->makeOrder();
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    /** @test */
    public function order_total_is_cast_to_decimal(): void
    {
        $order = $this->makeOrder(['subtotal' => 25.50, 'delivery_fee' => 5.00, 'discount' => 2.50, 'total' => 28.00]);
        $order->refresh();
        $this->assertEquals('28.00', $order->total);
        $this->assertEquals('5.00', $order->delivery_fee);
        $this->assertEquals('2.50', $order->discount);
    }

    /** @test */
    public function order_status_transitions(): void
    {
        $order = $this->makeOrder();
        foreach (['confirmed', 'baking', 'ready', 'delivered'] as $status) {
            $order->update(['status' => $status]);
            $this->assertEquals($status, $order->fresh()->status);
        }
    }

    /** @test */
    public function order_can_be_cancelled(): void
    {
        $order = $this->makeOrder();
        $order->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function order_belongs_to_user(): void
    {
        $order = $this->makeOrder();
        $this->assertInstanceOf(User::class, $order->user);
    }

    /** @test */
    public function order_item_total_price_attribute(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);
        $order = $this->makeOrder(['subtotal' => 15.00, 'total' => 15.00]);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3, 'unit_price' => 5.00]);
        $this->assertEquals(15.00, $item->total_price);
    }
}
