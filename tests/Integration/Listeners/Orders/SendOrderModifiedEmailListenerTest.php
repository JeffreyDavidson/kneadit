<?php

use App\Events\Orders\OrderModified;
use App\Listeners\Orders\SendOrderModifiedEmailListener;
use App\Mail\Orders\OrderModifiedMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('sends the modification email to the customer with the previous totals', function () {
    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new OrderModified($order, Money::fromDollars(20), Money::fromDollars(22));

    (new SendOrderModifiedEmailListener)->handle($event);

    Mail::assertQueued(
        OrderModifiedMail::class,
        fn (OrderModifiedMail $mail) => $mail->hasTo('buyer@example.com')
            && $mail->order->is($order)
            && $mail->previousSubtotal->cents() === 2000
            && $mail->previousTotal->cents() === 2200,
    );
});

test('does not send when the order has no customer', function () {
    $order = Order::factory()->create();
    $order->setRelation('customer', null);

    (new SendOrderModifiedEmailListener)->handle(
        new OrderModified($order, Money::fromDollars(20), Money::fromDollars(22)),
    );

    Mail::assertNothingQueued();
});

test('failed method logs a warning with the order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendOrderModifiedEmailListener failed'
            && $context['order'] === 'ORD-MOD-9'
            && $context['error'] === 'SMTP timeout');

    $order = Order::factory()->create(['order_number' => 'ORD-MOD-9']);

    (new SendOrderModifiedEmailListener)->failed(
        new OrderModified($order, Money::fromDollars(20), Money::fromDollars(22)),
        new RuntimeException('SMTP timeout'),
    );
});
