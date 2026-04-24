<?php

use App\Actions\Orders\RefundStripePayment;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Stripe\Exception\InvalidRequestException;
use Stripe\Service\RefundService;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('returns null when payment_status is not Paid', function () {
    $order = Order::factory()->unpaid()->create();

    $result = resolve(RefundStripePayment::class)($order);

    expect($result)->toBeNull();
});

test('returns null when no Stripe payment intent is recorded', function () {
    $order = Order::factory()->paid()->create(['stripe_payment_intent_id' => null]);

    $result = resolve(RefundStripePayment::class)($order);

    expect($result)->toBeNull();
});

test('refunds via Stripe, records a Refund row, and flips payment_status to Refunded', function () {
    $stripeRefundResource = (object) ['id' => 're_test_xyz123'];

    $refundService = mock(RefundService::class, function (MockInterface $m) use ($stripeRefundResource): void {
        $m->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $payload): bool => $payload['payment_intent'] === 'pi_test_abc'))
            ->andReturn($stripeRefundResource);
    });

    app()->bind(StripeClient::class, function () use ($refundService): StripeClient {
        $client = mock(StripeClient::class);
        $client->refunds = $refundService;

        return $client;
    });

    $user = User::factory()->owner()->create();
    $order = Order::factory()->paid()->create([
        'stripe_payment_intent_id' => 'pi_test_abc',
        'total' => 25.00,
    ]);

    $refund = resolve(RefundStripePayment::class)($order, initiatedBy: $user, reason: 'Customer requested refund');

    expect($refund)->toBeInstanceOf(Refund::class)
        ->and($refund->stripe_refund_id)->toBe('re_test_xyz123')
        ->and($refund->amount->dollars())->toBe(25.00)
        ->and($refund->reason)->toBe('Customer requested refund')
        ->and($refund->user_id)->toBe($user->id);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Refunded);
});

test('throws StripeRefundFailedException when the Stripe API errors', function () {
    $stripeError = InvalidRequestException::factory('Charge has already been refunded.', 400, null, null);

    $refundService = mock(RefundService::class, function (MockInterface $m) use ($stripeError): void {
        $m->shouldReceive('create')->once()->andThrow($stripeError);
    });

    app()->bind(StripeClient::class, function () use ($refundService): StripeClient {
        $client = mock(StripeClient::class);
        $client->refunds = $refundService;

        return $client;
    });

    $order = Order::factory()->paid()->create([
        'stripe_payment_intent_id' => 'pi_already_refunded',
        'total' => 10.00,
    ]);

    expect(fn () => resolve(RefundStripePayment::class)($order))
        ->toThrow(StripeRefundFailedException::class);

    // No Refund row written, payment_status unchanged.
    expect(Refund::query()->count())->toBe(0)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});
