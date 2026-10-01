<?php

use App\Actions\Stripe\HandleCheckoutComplete;
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
