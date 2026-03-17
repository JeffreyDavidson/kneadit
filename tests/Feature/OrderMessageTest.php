<?php

use App\Mail\NewOrderMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    $user = User::create(['name' => 'Baker', 'email' => 'baker@test.com', 'password' => bcrypt('pass')]);
    $customer = Customer::create(['name' => 'Test', 'email' => 'test@example.com']);
    $this->order = Order::create([
        'order_number' => 'ORD-MSG-001',
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => 'confirmed',
        'subtotal' => 50.00,
        'total' => 50.00,
    ]);
});

test('messages endpoint returns order messages', function () {
    OrderMessage::create([
        'order_id' => $this->order->id,
        'sender_type' => 'baker',
        'sender_name' => 'Baker Bob',
        'message' => 'Your order is being prepared!',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/{$this->order->id}/messages");

    $response->assertOk();
    $response->assertJsonPath('messages.0.message', 'Your order is being prepared!');
});

test('customer can send message on their order', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->id}/messages", [
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
        ->postJson("/order/{$this->order->id}/messages", [
            'message' => 'Hello!',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    $msg = OrderMessage::first();
    expect($msg->sender_type)->toBe('customer');
});

test('messages require content', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->id}/messages", [
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
        ->postJson("/order/{$this->order->id}/messages", [
            'message' => 'Question about my order',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    Mail::assertQueued(NewOrderMessage::class);
});
