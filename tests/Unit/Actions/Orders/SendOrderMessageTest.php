<?php

use App\Actions\Orders\SendOrderMessage;
use App\Enums\SenderType;
use App\Events\OrderMessageSent;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

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
