<?php

use App\Enums\SenderType;
use App\Mail\NewOrderMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    $user = User::query()->create(['name' => 'Baker', 'email' => 'baker@test.com', 'password' => bcrypt('pass')]);
    $customer = Customer::query()->create(['name' => 'Test', 'email' => 'test@example.com']);
    $this->order = Order::query()->create([
        'order_number' => 'ORD-MSG-001',
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => 'confirmed',
        'subtotal' => 50.00,
        'total' => 50.00,
    ]);
});

test('messages endpoint returns order messages', function () {
    OrderMessage::query()->create([
        'order_id' => $this->order->id,
        'sender_type' => 'baker',
        'sender_name' => 'Baker Bob',
        'message' => 'Your order is being prepared!',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/{$this->order->order_number}/messages");

    $response->assertOk();
    $response->assertJsonPath('messages.0.message', 'Your order is being prepared!');
});

test('customer can send message on their order', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->order_number}/messages", [
            'message' => 'Can I add extra frosting?',
            'sender_name' => 'Test Customer',
            'sender_email' => 'test@example.com',
        ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $this->assertDatabaseHas('order_messages', [
        'order_id' => $this->order->id,
        'message' => 'Can I add extra frosting?',
    ]);
});

test('message is saved with correct sender type', function () {
    Mail::fake();

    withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->order_number}/messages", [
            'message' => 'Hello!',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    $msg = OrderMessage::query()->first();
    expect($msg->sender_type)->toBe(SenderType::Customer);
});

test('messages require content', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->order_number}/messages", [
            'sender_name' => 'Test',
            'sender_email' => 'test@example.com',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['message']);
});

test('notification email is sent to baker', function () {
    Mail::fake();
    Setting::set('store_email', 'baker@bakery.com');

    withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->order_number}/messages", [
            'message' => 'Question about my order',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    Mail::assertQueued(NewOrderMessage::class);
});
