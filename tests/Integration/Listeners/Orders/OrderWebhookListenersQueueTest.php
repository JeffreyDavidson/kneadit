<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\DispatchOrderCancelledWebhookListener;
use App\Listeners\Orders\DispatchOrderCreatedWebhookListener;
use App\Listeners\Orders\DispatchOrderDeliveredWebhookListener;
use App\Listeners\Orders\DispatchOrderWebhookListener;
use App\Models\Orders\Order;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Testing\Fakes\QueueFake;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function queuedListenerCount(QueueFake $queue, string $listener): int
{
    return $queue->pushed(CallQueuedListener::class)
        ->filter(fn (CallQueuedListener $job): bool => $job->class === $listener)
        ->count();
}

test('every order is queued for the webhook listener while another is already queued', function (string $listener, Closure $raiseEvent) {
    $queue = Queue::fake();

    $orders = Order::factory()->count(2)->create();

    $orders->each(fn (Order $order) => $raiseEvent($order));

    expect(queuedListenerCount($queue, $listener))->toBe(2);
})->with([
    'order.created' => [
        DispatchOrderCreatedWebhookListener::class,
        OrderCreated::dispatch(...),
    ],
    'order.updated' => [
        DispatchOrderWebhookListener::class,
        fn (Order $order) => OrderStatusChanged::dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed),
    ],
    'order.cancelled' => [
        DispatchOrderCancelledWebhookListener::class,
        fn (Order $order) => OrderCancelled::dispatch($order, OrderStatus::Pending),
    ],
    'order.delivered' => [
        DispatchOrderDeliveredWebhookListener::class,
        fn (Order $order) => OrderDelivered::dispatch($order, OrderStatus::Ready),
    ],
]);

test('different status transitions of the same order are each queued', function () {
    $queue = Queue::fake();

    $order = Order::factory()->create();

    OrderStatusChanged::dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);
    OrderStatusChanged::dispatch($order, OrderStatus::Confirmed, OrderStatus::Ready);

    expect(queuedListenerCount($queue, DispatchOrderWebhookListener::class))->toBe(2);
});
