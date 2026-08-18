<?php

use App\Actions\Orders\SendOrderMessage;
use App\Enums\Orders\SenderType;
use App\Events\Orders\OrderMessageSent;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it creates a message and dispatches event', function () {
    Event::fake([OrderMessageSent::class]);

    $order = Order::factory()->create();

    $action = new SendOrderMessage;
    $message = $action(
        order: $order,
        senderName: 'Jane Baker',
        message: 'When will my order be ready?',
    );

    expect($message)->toBeInstanceOf(OrderMessage::class)
        ->and($message->sender_type)->toBe(SenderType::Customer)
        ->and($message->sender_name)->toBe('Jane Baker')
        ->and($message->message)->toBe('When will my order be ready?');

    Event::assertDispatched(OrderMessageSent::class);
});

test('baker message marks unread customer messages as read', function () {
    Event::fake([OrderMessageSent::class]);

    $order = Order::factory()->create();
    $order->messages()->create([
        'sender_type' => SenderType::Customer,
        'sender_name' => 'Customer',
        'message' => 'Hello?',
        'is_read' => false,
    ]);

    $action = new SendOrderMessage;
    $action(
        order: $order,
        senderName: 'Baker Jane',
        message: 'Your order is ready!',
        senderType: SenderType::Baker,
    );

    expect($order->messages()->where('sender_type', SenderType::Customer)->first()->is_read)->toBeTrue();
});
