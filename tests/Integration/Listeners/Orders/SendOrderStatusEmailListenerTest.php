<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\SendOrderStatusEmailListener;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create(['email' => 'buyer@example.com']);
});

dataset('emailableTransitions', [
    'confirmed' => [OrderStatus::Pending, OrderStatus::Confirmed],
    'baking' => [OrderStatus::Confirmed, OrderStatus::Baking],
    'ready' => [OrderStatus::Baking, OrderStatus::Ready],
    'delivered' => [OrderStatus::Ready, OrderStatus::Delivered],
    'cancelled' => [OrderStatus::Confirmed, OrderStatus::Cancelled],
]);

test('sends the correct email for each emailable status', function (OrderStatus $from, OrderStatus $to) {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    (new SendOrderStatusEmailListener)->handle(new OrderStatusChanged($order, $from, $to));

    Mail::assertQueued(
        OrderStatusMail::class,
        fn (OrderStatusMail $mail) => $mail->status === $to && $mail->hasTo('buyer@example.com'),
    );
})->with('emailableTransitions');

test('does not send for non-emailable statuses', function () {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    (new SendOrderStatusEmailListener)->handle(
        new OrderStatusChanged($order, OrderStatus::Confirmed, OrderStatus::Pending),
    );

    Mail::assertNothingQueued();
});

test('does not send when customer has no email', function () {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();
    $order->setRelation('customer', null);

    (new SendOrderStatusEmailListener)->handle(
        new OrderStatusChanged($order, OrderStatus::Pending, OrderStatus::Confirmed),
    );

    Mail::assertNothingQueued();
});

test('does not send when the per-status email toggle is disabled', function () {
    settings(['email_order_baking_enabled' => false]);
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    (new SendOrderStatusEmailListener)->handle(
        new OrderStatusChanged($order, OrderStatus::Confirmed, OrderStatus::Baking),
    );

    Mail::assertNothingQueued();
});

test('still sends other statuses when only one toggle is disabled', function () {
    settings(['email_order_baking_enabled' => false]);
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    (new SendOrderStatusEmailListener)->handle(
        new OrderStatusChanged($order, OrderStatus::Baking, OrderStatus::Ready),
    );

    Mail::assertQueued(OrderStatusMail::class);
});
