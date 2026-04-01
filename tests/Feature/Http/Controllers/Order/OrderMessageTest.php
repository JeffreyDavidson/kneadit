<?php

use App\Enums\Orders\SenderType;
use App\Mail\Orders\NewOrderMessageMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();
    $this->order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create(['order_number' => 'ORD-MSG-001']);
});

test('messages endpoint returns order messages', function () {
    OrderMessage::factory()
        ->for($this->order)
        ->fromBaker()
        ->create([
            'sender_name' => 'Baker Bob',
            'message' => 'Your order is being prepared!',
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/order/{$this->order->order_number}/messages");

    $response->assertOk();
    $response->assertJsonPath('data.0.message', 'Your order is being prepared!');
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
    $response->assertJsonPath('message', 'Message sent successfully.');
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
    settings(['store_email' => 'baker@bakery.com']);

    withoutMiddleware(tenantMiddleware())
        ->postJson("/order/{$this->order->order_number}/messages", [
            'message' => 'Question about my order',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    Mail::assertQueued(NewOrderMessageMail::class);
});
