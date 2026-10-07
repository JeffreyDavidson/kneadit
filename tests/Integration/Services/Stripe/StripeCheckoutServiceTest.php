<?php

use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Orders\OrderModificationGuard;
use App\Services\Stripe\StripeCheckoutService;
use App\Services\Stripe\StripeSettingsReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stripe\Checkout\Session;
use Stripe\Exception\InvalidRequestException;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;
use Tests\Support\Stripe\FakeCheckoutStripeClient;

pest()->use(RefreshDatabase::class);

final class FakeStripeCheckoutClient extends StripeClient
{
    public object $checkout;

    public function __construct(SessionService $sessions)
    {
        $this->checkout = (object) ['sessions' => $sessions];
    }
}

function fakePaidCheckoutSession(int $amountTotal): void
{
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(Session::constructFrom([
        'id' => 'cs_test_paid',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_paid',
        'amount_total' => $amountTotal,
    ]));

    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeCheckoutClient($sessions));
}

beforeEach(function () {
    setUpTenantTest();
    config(['cashier.secret' => 'sk_test_fake_key']);
});

test('isEnabled returns false when stripe not in payment methods', function () {
    settings(['payment_methods' => json_encode(['paypal'])]);

    expect(resolve(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns false when no connect id', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => null,
    ]);

    expect(resolve(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns false when charges not enabled', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '0',
    ]);

    expect(resolve(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns true when stripe fully configured', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);

    expect(resolve(StripeSettingsReader::class)->isEnabled())->toBeTrue();
});

test('isEnabled returns false when payment methods is null', function () {
    settings(['payment_methods' => null]);

    expect(resolve(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('connectId returns stored connect id', function () {
    settings(['stripe_connect_id' => 'acct_123abc']);

    expect(resolve(StripeSettingsReader::class)->connectId())->toBe('acct_123abc');
});

test('connectId returns null when not set', function () {
    settings(['stripe_connect_id' => null]);

    expect(resolve(StripeSettingsReader::class)->connectId())->toBeNull();
});

test('redirectToCheckout returns null when total is zero', function () {
    $order = Order::factory()->create(['total' => 0]);

    $service = resolve(StripeCheckoutService::class);
    expect($service->redirectToCheckout($order))->toBeNull();
});

test('redirectToCheckout returns null when stripe not enabled', function () {
    settings(['payment_methods' => json_encode(['paypal'])]);

    $order = Order::factory()->create(['total' => 50.00]);

    $service = resolve(StripeCheckoutService::class);
    expect($service->redirectToCheckout($order))->toBeNull();
});

test('createCheckoutSession returns null when no connect id', function () {
    settings(['stripe_connect_id' => null]);

    $order = Order::factory()->create();

    $service = resolve(StripeCheckoutService::class);
    $result = $service->createCheckoutSession($order, 'https://success.com', 'https://cancel.com');

    expect($result)->toBeNull();
});

test('handleCheckoutComplete returns null when no connect id', function () {
    settings(['stripe_connect_id' => null]);

    $service = resolve(StripeCheckoutService::class);
    $result = $service->handleCheckoutComplete('cs_test_123');

    expect($result)->toBeNull();
});

test('handleCheckoutComplete marks the order paid when the amount paid equals the order total', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $order = Order::factory()->unpaid()->create(['stripe_checkout_session_id' => 'cs_test_paid', 'total' => 50.00]);
    fakePaidCheckoutSession(5000);

    $result = resolve(StripeCheckoutService::class)->handleCheckoutComplete('cs_test_paid');

    expect($result?->is($order))->toBeTrue()
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_test_paid');
});

test('handleCheckoutComplete leaves the order unpaid and notifies the baker when the amount paid differs', function (int $amountPaid) {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message): bool => str_contains($message, 'amount does not match'));
    settings(['stripe_connect_id' => 'acct_test']);
    $owner = User::factory()->owner()->create();
    $order = Order::factory()->unpaid()->create(['stripe_checkout_session_id' => 'cs_test_paid', 'total' => 50.00]);
    fakePaidCheckoutSession($amountPaid);

    $result = resolve(StripeCheckoutService::class)->handleCheckoutComplete('cs_test_paid');

    expect($result?->is($order))->toBeTrue()
        ->and($result->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_test_paid')
        ->and($owner->notifications()->count())->toBe(1);
})->with([
    'lower than the order total' => 4000,
    'higher than the order total' => 6000,
]);

/**
 * @param  array<string, mixed>  $attributes
 */
function fakeOrderCheckoutSession(array $attributes = []): Session
{
    return Session::constructFrom([
        'id' => 'cs_test_stored',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'url' => 'https://checkout.stripe.test/c/pay/cs_test_stored',
        ...$attributes,
    ]);
}

function enableStripeForOrders(): void
{
    app()->instance(TenantContract::class, new Tenant(['id' => 'test-bakery']));
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);
}

test('resumeOrCreateCheckout reuses the stored session while Stripe reports it open', function () {
    enableStripeForOrders();
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_test_stored', 'total' => 50.00]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(fakeOrderCheckoutSession());
    $sessions->expects('create')->never();

    $url = resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_stored')
        ->and($order->refresh()->stripe_checkout_session_id)->toBe('cs_test_stored');
});

test('resumeOrCreateCheckout replaces an expired session with a new one', function () {
    enableStripeForOrders();
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_test_stored', 'total' => 50.00]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(fakeOrderCheckoutSession(['status' => 'expired', 'url' => null]));
    $sessions->expects('create')->returns(fakeOrderCheckoutSession([
        'id' => 'cs_test_new',
        'url' => 'https://checkout.stripe.test/c/pay/cs_test_new',
    ]));

    $url = resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_new')
        ->and($order->refresh())
        ->stripe_checkout_session_id->toBe('cs_test_new')
        ->payment_status->toBe(PaymentStatus::Unpaid);
});

test('resumeOrCreateCheckout forgets an expired session it cannot replace so the order can be edited again', function () {
    enableStripeForOrders();
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_test_stored', 'total' => 50.00]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(fakeOrderCheckoutSession(['status' => 'expired', 'url' => null]));
    $sessions->expects('create')->throws(InvalidRequestException::factory('Stripe is unavailable.', 400, null, null));

    $url = resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order);

    expect($url)->toBeNull()
        ->and($order->refresh()->stripe_checkout_session_id)->toBeNull()
        ->and(resolve(OrderModificationGuard::class)->hasOpenCheckout($order))->toBeFalse();
});

test('resumeOrCreateCheckout sends a customer whose stored session was already paid to the success page to record it', function () {
    enableStripeForOrders();
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_test_stored', 'total' => 50.00]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(fakeOrderCheckoutSession(['status' => 'complete', 'payment_status' => 'paid', 'url' => null]));
    $sessions->expects('create')->never();

    $url = resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order);

    expect($url)->toBe(route('order.stripe.success', $order).'?session_id=cs_test_stored');
});

test('resumeOrCreateCheckout creates a session for an order that has none', function () {
    enableStripeForOrders();
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->never();
    $sessions->expects('create')->returns(fakeOrderCheckoutSession(['id' => 'cs_test_first']));

    $url = resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_stored')
        ->and($order->refresh()->stripe_checkout_session_id)->toBe('cs_test_first');
});

test('resumeOrCreateCheckout returns null when Stripe is not enabled', function () {
    settings(['payment_methods' => json_encode(['cash'])]);
    $order = Order::factory()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    expect(resolve(StripeCheckoutService::class)->resumeOrCreateCheckout($order))->toBeNull();
});
