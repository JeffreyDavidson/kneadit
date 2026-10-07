<?php

use App\Actions\Stripe\HandleCheckoutComplete;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();

    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

test('updates order with stripe payment intent id', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_test_123', $order->total->cents());

    expect($order->refresh()->stripe_payment_intent_id)->toBe('pi_test_123');
});

test('marks order as paid', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_test_456', $order->total->cents());

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('returns the updated order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    $result = resolve(HandleCheckoutComplete::class)($order, 'pi_test_789', $order->total->cents());

    expect($result)->toBeInstanceOf(Order::class)
        ->id->toBe($order->id);
});

dataset('mismatched stripe amounts', [
    'lower than the order total' => -500,
    'higher than the order total' => 500,
]);

test('leaves the order unpaid and records the payment intent when the amount paid differs', function (int $difference) {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => str_contains($message, 'amount does not match')
            && $context['paid_cents'] === 5000 + $difference
            && $context['order_total_cents'] === 5000);
    $owner = test()->user;
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->unpaid()
        ->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    $result = resolve(HandleCheckoutComplete::class)($order, 'pi_mismatch', 5000 + $difference);

    expect($result->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_mismatch')
        ->and($owner->notifications()->count())->toBe(1);
})->with('mismatched stripe amounts');

test('does not notify the baker again when the same payment intent is flagged twice', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->unpaid()
        ->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_mismatch', 4000);
    resolve(HandleCheckoutComplete::class)($order->refresh(), 'pi_mismatch', 4000);

    expect(test()->user->notifications()->count())->toBe(1);
});

test('a payment that arrives after the order was cancelled is not applied and the baker is told once', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->cancelled()
        ->unpaid()
        ->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_late_cancelled', 5000);
    resolve(HandleCheckoutComplete::class)($order->refresh(), 'pi_late_cancelled', 5000);

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Unpaid)
        ->stripe_payment_intent_id->toBeNull()
        ->and(test()->user->notifications()->count())->toBe(1)
        ->and(test()->user->notifications()->first()->data['body'])->toContain('pi_late_cancelled')->toContain('cancelled');
});

test('a card payment that arrives after the order was marked paid another way leaves the payment untouched and the baker is told', function (?string $firstPaymentIntent) {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->confirmed()
        ->paid()
        ->create([
            'payment_method' => $firstPaymentIntent === null ? PaymentMethod::Cash : PaymentMethod::Stripe,
            'stripe_payment_intent_id' => $firstPaymentIntent,
            'total' => 50.00,
        ]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_second', 5000);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->stripe_payment_intent_id->toBe($firstPaymentIntent)
        ->and(test()->user->notifications()->count())->toBe(1)
        ->and(test()->user->notifications()->first()->data['body'])->toContain('pi_second');
})->with([
    'paid in cash' => [null],
    'paid by another card payment' => ['pi_first'],
]);

test('replaying the completion of the payment that already paid the order changes nothing and tells nobody', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->unpaid()
        ->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_once', 5000);
    resolve(HandleCheckoutComplete::class)($order->refresh(), 'pi_once', 5000);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed)
        ->stripe_payment_intent_id->toBe('pi_once')
        ->and(test()->user->notifications()->count())->toBe(0);
});

test('replaying a payment on an order cancelled and refunded since leaves it refunded and tells nobody', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->cancelled()
        ->create([
            'payment_method' => PaymentMethod::Stripe,
            'payment_status' => PaymentStatus::Refunded,
            'stripe_payment_intent_id' => 'pi_refunded',
            'total' => 50.00,
        ]);

    resolve(HandleCheckoutComplete::class)($order, 'pi_refunded', 5000);

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Refunded)
        ->and(test()->user->notifications()->count())->toBe(0);
});
