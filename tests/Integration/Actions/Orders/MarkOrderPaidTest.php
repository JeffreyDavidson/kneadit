<?php

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\InvalidRequestException;
use Tests\Support\Stripe\FakeCheckoutStripeClient;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

test('marks order as paid', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('auto-confirms pending stripe order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('auto-confirms pending paypal order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::PayPal]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('does not auto-confirm cash order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Cash]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Pending);
});

test('does not auto-confirm other payment method order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Other]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Pending);
});

test('is idempotent when called twice', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(MarkOrderPaid::class)($order);
    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('skips status transition when order is already confirmed', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->confirmed()
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('skips status transition when order is already beyond confirmed', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->baking()
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Baking);
});

test('a stale copy of an order another request already paid and confirmed is left as it is', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);
    $stale = Order::query()->findOrFail($order->id);

    resolve(MarkOrderPaid::class)($order);
    $result = resolve(MarkOrderPaid::class)($stale);

    expect($result)
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed)
        ->and($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('marking a cash order paid expires its open Stripe checkout session', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->with('cs_test_open', [], ['stripe_account' => 'acct_test']);
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Cash, 'stripe_checkout_session_id' => 'cs_test_open']);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->stripe_checkout_session_id->toBe('cs_test_open');
});

test('a refused session expiry does not stop the order being marked paid', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->throws(InvalidRequestException::factory('Session already complete.', 400, null, null));
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Cash, 'stripe_checkout_session_id' => 'cs_test_open']);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('marking an order paid after Stripe collected the payment leaves its session alone', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->never();
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create([
            'payment_method' => PaymentMethod::Stripe,
            'stripe_checkout_session_id' => 'cs_test_paid',
            'stripe_payment_intent_id' => 'pi_test_paid',
        ]);

    resolve(MarkOrderPaid::class)($order);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});
