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
    test()->order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create(['order_number' => 'ORD-MSG-001']);
});

test('messages endpoint returns order messages', function () {
    OrderMessage::factory()
        ->for(testFixture('order', Order::class))
        ->fromBaker()
        ->create([
            'sender_name' => 'Baker Bob',
            'message' => 'Your order is being prepared!',
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->getJson(route('order.messages', testFixture('order', Order::class)->order_number, false));

    $response->assertOk();
    $response->assertJsonPath('data.0.message', 'Your order is being prepared!');
});

test('customer can send message on their order', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->postJson(route('order.messages.send', testFixture('order', Order::class)->order_number, false), [
            'message' => 'Can I add extra frosting?',
            'sender_name' => 'Test Customer',
            'sender_email' => 'test@example.com',
        ]);

    $response->assertOk();
    $response->assertJsonPath('message', 'Message sent successfully.');
    test()->assertDatabaseHas('order_messages', [
        'order_id' => testFixture('order', Order::class)->id,
        'message' => 'Can I add extra frosting?',
    ]);
});

test('message is saved with correct sender type', function () {
    Mail::fake();

    withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->postJson(route('order.messages.send', testFixture('order', Order::class)->order_number, false), [
            'message' => 'Hello!',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    $msg = OrderMessage::query()->first();
    expect($msg->sender_type)->toBe(SenderType::Customer);
});

test('messages require content', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->postJson(route('order.messages.send', testFixture('order', Order::class)->order_number, false), [
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
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->postJson(route('order.messages.send', testFixture('order', Order::class)->order_number, false), [
            'message' => 'Question about my order',
            'sender_name' => 'Customer',
            'sender_email' => 'cust@example.com',
        ]);

    Mail::assertQueued(NewOrderMessageMail::class);
});
