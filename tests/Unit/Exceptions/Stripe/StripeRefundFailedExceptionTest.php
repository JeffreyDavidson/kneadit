<?php

use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Orders\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

test('builds a descriptive message containing order number and payment intent', function () {
    $order = new Order(['order_number' => 'KN-1234']);
    $previous = InvalidRequestException::factory('Charge already refunded', 400, null, null);

    $exception = new StripeRefundFailedException(
        order: $order,
        paymentIntentId: 'pi_test_abc',
        stripeErrorCode: 'charge_already_refunded',
        previous: $previous,
    );

    expect($exception->getMessage())
        ->toContain('KN-1234')
        ->toContain('pi_test_abc')
        ->toContain('Charge already refunded');
});

test('exposes structured context for logging', function () {
    $order = new Order(['order_number' => 'KN-9999']);
    $order->id = 42;
    $previous = InvalidRequestException::factory('Test', 400, null, null);

    $exception = new StripeRefundFailedException(
        order: $order,
        paymentIntentId: 'pi_xyz',
        stripeErrorCode: 'card_declined',
        previous: $previous,
    );

    expect($exception->context())->toBe([
        'order_id' => 42,
        'order_number' => 'KN-9999',
        'payment_intent_id' => 'pi_xyz',
        'stripe_error_code' => 'card_declined',
        'stripe_message' => 'Test',
    ]);
});

test('chains the original Stripe exception', function () {
    $order = new Order(['order_number' => 'KN-1']);
    $previous = InvalidRequestException::factory('Something broke', 400, null, null);

    $exception = new StripeRefundFailedException(
        order: $order,
        paymentIntentId: 'pi_1',
        stripeErrorCode: null,
        previous: $previous,
    );

    expect($exception->getPrevious())->toBe($previous)
        ->and($exception->getPrevious())->toBeInstanceOf(ApiErrorException::class);
});
