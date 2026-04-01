<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\SendOrderStatusEmailListener;
use App\Mail\Orders\OrderBakingMail;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Orders\OrderReadyMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('statusToMailable', [
    'confirmed' => [OrderStatus::Confirmed, OrderConfirmedMail::class],
    'baking' => [OrderStatus::Baking, OrderBakingMail::class],
    'ready' => [OrderStatus::Ready, OrderReadyMail::class],
    'delivered' => [OrderStatus::Delivered, OrderDeliveredMail::class],
    'cancelled' => [OrderStatus::Cancelled, OrderCancelledMail::class],
]);

test('it sends the correct email for each status transition', function (OrderStatus $status, string $mailableClass) {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new OrderStatusChanged($order, OrderStatus::Pending, $status);

    $listener = new SendOrderStatusEmailListener;
    $listener->handle($event);

    Mail::assertQueued(
        $mailableClass,
        fn ($mail) => $mail->hasTo('customer@example.com'),
    );
})->with('statusToMailable');

test('it does not send email for pending status', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'test@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new OrderStatusChanged($order, OrderStatus::Confirmed, OrderStatus::Pending);

    $listener = new SendOrderStatusEmailListener;
    $listener->handle($event);

    Mail::assertNothingQueued();
});
