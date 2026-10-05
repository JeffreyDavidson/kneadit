<?php

use App\Actions\Orders\RefundStripePayment;
use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Loyalty\CustomerLoyalty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Stripe\Exception\InvalidRequestException;
use Stripe\Service\RefundService;
use Stripe\StripeClient;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

final class FakeStripeRefundClient extends StripeClient
{
    public function __construct(public RefundService $refunds) {}
}

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

    $refundService = Double::for(RefundService::class);
    $refundService->expects('create')
        ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['payment_intent'] ?? null) === 'pi_test_abc'))
        ->returns($stripeRefundResource);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeRefundClient($refundService));

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
        ->and($refund->user_id)->toBe($user->id)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Refunded);
});

test('throws StripeRefundFailedException when the Stripe API errors', function () {
    $stripeError = InvalidRequestException::factory('Charge has already been refunded.', 400, null, null);

    $refundService = Double::for(RefundService::class);
    $refundService->expects('create')->throws($stripeError);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeRefundClient($refundService));

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

test('refunds connected payments through the tenant Stripe account', function () {
    settings(['stripe_connect_id' => 'acct_test_connect']);

    $stripeRefundResource = (object) ['id' => 're_test_connect'];
    $refundService = Double::for(RefundService::class);
    $refundService->expects('create')
        ->with(
            Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['payment_intent'] ?? null) === 'pi_test_connect'),
            ['stripe_account' => 'acct_test_connect'],
        )
        ->returns($stripeRefundResource);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeRefundClient($refundService));

    $order = Order::factory()->paid()->create(['stripe_payment_intent_id' => 'pi_test_connect']);

    $refund = resolve(RefundStripePayment::class)($order);

    expect($refund?->stripe_refund_id)->toBe('re_test_connect');
});

function fakeStripeRefundSucceeding(): void
{
    $refundService = Double::for(RefundService::class);
    $refundService->allows('create')->returns((object) ['id' => 're_test_points']);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeRefundClient($refundService));
}

test('a full refund takes back the points the order earned, once', function () {
    fakeStripeRefundSucceeding();
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->for($customer)->earned(40)->create(['order_id' => null]);
    $order = Order::factory()->for($customer)->paid()->create(['stripe_payment_intent_id' => 'pi_test_points']);
    LoyaltyPoint::factory()->for($customer)->earned(250)->create(['order_id' => $order->id]);

    resolve(RefundStripePayment::class)($order);
    resolve(RefundStripePayment::class)($order->fresh());

    $reversals = LoyaltyPoint::query()->forOrder($order)->where('type', LoyaltyPointType::Reversed)->get();
    expect($reversals)->toHaveCount(1)
        ->and($reversals->first()->points)->toBe(250)
        ->and(resolve(CustomerLoyalty::class)->balance($customer)->total)->toBe(40);
});

test('a failed Stripe refund leaves the order points alone', function () {
    $refundService = Double::for(RefundService::class);
    $refundService->expects('create')->throws(InvalidRequestException::factory('Nope.', 400, null, null));
    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeRefundClient($refundService));
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->paid()->create(['stripe_payment_intent_id' => 'pi_test_failed']);
    LoyaltyPoint::factory()->for($customer)->earned(250)->create(['order_id' => $order->id]);

    expect(fn () => resolve(RefundStripePayment::class)($order))->toThrow(StripeRefundFailedException::class)
        ->and(resolve(CustomerLoyalty::class)->balance($customer)->total)->toBe(250);
});
