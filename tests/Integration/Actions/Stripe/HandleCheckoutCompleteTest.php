<?php

use App\Actions\Stripe\HandleCheckoutComplete;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
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

    app(HandleCheckoutComplete::class)($order, 'pi_test_123');

    expect($order->refresh()->stripe_payment_intent_id)->toBe('pi_test_123');
});

test('marks order as paid', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    app(HandleCheckoutComplete::class)($order, 'pi_test_456');

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('returns the updated order', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['payment_method' => PaymentMethod::Stripe]);

    $result = app(HandleCheckoutComplete::class)($order, 'pi_test_789');

    expect($result)->toBeInstanceOf(Order::class)
        ->id->toBe($order->id);
});
