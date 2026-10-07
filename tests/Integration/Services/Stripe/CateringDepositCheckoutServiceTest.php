<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stripe\Checkout\Session;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;

pest()->use(RefreshDatabase::class);

final class FakeCateringStripeClient extends StripeClient
{
    public object $checkout;

    public function __construct(SessionService $sessions)
    {
        $this->checkout = (object) ['sessions' => $sessions];
    }
}

/**
 * @param  array<string, mixed>  $attributes
 */
function fakeCateringStripeSession(CateringInquiry $inquiry, array $attributes = []): Session
{
    return Session::constructFrom([
        'id' => 'cs_test_older',
        'status' => 'complete',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_older',
        'amount_total' => 25000,
        'url' => null,
        'metadata' => ['catering_inquiry_id' => (string) $inquiry->id, 'tenant_id' => 'test-bakery'],
        ...$attributes,
    ]);
}

function bindCateringStripeSessions(SessionService $sessions): void
{
    app()->bind(StripeClient::class, fn (): StripeClient => new FakeCateringStripeClient($sessions));
}

beforeEach(function () {
    setUpTenantTest();
    config(['cashier.secret' => 'sk_test_fake_key']);
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);
});

test('a paid session is matched to its inquiry by metadata, not by the latest stored session id', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_newer']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry));
    bindCateringStripeSessions($sessions);

    $result = resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');

    expect($result?->is($inquiry))->toBeTrue()
        ->and($inquiry->refresh()->deposit_paid_at)->not->toBeNull()
        ->and($inquiry->deposit_amount?->dollars())->toBe(250.00)
        ->and($inquiry->deposit_reference)->toBe('pi_test_older')
        ->and($inquiry->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('a paid session without an inquiry in its metadata is ignored', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_older']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry, ['metadata' => []]));
    bindCateringStripeSessions($sessions);

    $result = resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');

    expect($result)->toBeNull()
        ->and($inquiry->refresh()->deposit_paid_at)->toBeNull();
});

test('a paid session for an inquiry that no longer accepts deposits is not recorded and the owner is notified once', function (CateringInquiryStatus $status) {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $context['session_id'] === 'cs_test_older'
            && $context['payment_intent'] === 'pi_test_older');
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->create(['status' => $status]);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->times(2)->returns(fakeCateringStripeSession($inquiry));
    bindCateringStripeSessions($sessions);

    resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');
    resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');

    expect($inquiry->refresh()->deposit_paid_at)->toBeNull()
        ->and($inquiry->status)->toBe($status)
        ->and($owner->notifications()->count())->toBe(1);
})->with([
    'cancelled' => CateringInquiryStatus::Cancelled,
    'completed' => CateringInquiryStatus::Completed,
    'inquiry' => CateringInquiryStatus::Inquiry,
]);

test('a second paid session for an inquiry with a recorded deposit leaves it unchanged and notifies the owner', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $context['payment_intent'] === 'pi_test_second');
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->confirmed()->create([
        'deposit_amount' => 100,
        'deposit_paid_at' => now()->subDay(),
        'deposit_reference' => 'pi_test_first',
        'stripe_payment_intent_id' => 'pi_test_first',
    ]);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry, ['id' => 'cs_test_second', 'payment_intent' => 'pi_test_second']));
    bindCateringStripeSessions($sessions);

    resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_second');

    expect($inquiry->refresh()->deposit_amount?->dollars())->toBe(100.00)
        ->and($inquiry->deposit_reference)->toBe('pi_test_first')
        ->and($inquiry->stripe_payment_intent_id)->toBe('pi_test_first')
        ->and($owner->notifications()->count())->toBe(1);
});

test('completing the same paid session twice records the deposit once without alerting the owner', function () {
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->quoted()->create();
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->times(2)->returns(fakeCateringStripeSession($inquiry));
    bindCateringStripeSessions($sessions);

    resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');
    resolve(CateringDepositCheckoutService::class)->handleCheckoutComplete('cs_test_older');

    expect($inquiry->refresh()->deposit_reference)->toBe('pi_test_older')
        ->and($owner->notifications()->count())->toBe(0);
});

test('redirectToCheckout reuses the stored session while it is still open', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_open']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry, [
        'id' => 'cs_test_open',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'url' => 'https://checkout.stripe.test/c/pay/cs_test_open',
    ]));
    $sessions->expects('create')->never();
    bindCateringStripeSessions($sessions);

    $url = resolve(CateringDepositCheckoutService::class)->redirectToCheckout($inquiry, 250.00);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_open')
        ->and($inquiry->refresh()->stripe_checkout_session_id)->toBe('cs_test_open');
});

test('redirectToCheckout creates a new session when the stored one has expired', function () {
    app()->instance(TenantContract::class, new Tenant(['id' => 'test-bakery']));
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_expired']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry, [
        'id' => 'cs_test_expired',
        'status' => 'expired',
        'payment_status' => 'unpaid',
    ]));
    $sessions->expects('create')->returns(fakeCateringStripeSession($inquiry, [
        'id' => 'cs_test_new',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'url' => 'https://checkout.stripe.test/c/pay/cs_test_new',
    ]));
    bindCateringStripeSessions($sessions);

    $url = resolve(CateringDepositCheckoutService::class)->redirectToCheckout($inquiry, 250.00);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_new')
        ->and($inquiry->refresh()->stripe_checkout_session_id)->toBe('cs_test_new');
});

test('redirectToCheckout records a stored session that was already paid and sends the customer to the signed success page', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_older']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('retrieve')->returns(fakeCateringStripeSession($inquiry));
    $sessions->expects('create')->never();
    bindCateringStripeSessions($sessions);

    $url = resolve(CateringDepositCheckoutService::class)->redirectToCheckout($inquiry, 250.00);

    expect($inquiry->refresh()->deposit_paid_at)->not->toBeNull()
        ->and($url)->toContain("/catering/stripe/success/{$inquiry->id}")
        ->and($url)->toContain('signature=');
});

test('a new deposit session accepts cards only', function () {
    app()->instance(TenantContract::class, new Tenant(['id' => 'test-bakery']));
    $inquiry = CateringInquiry::factory()->quoted()->create();
    $sessions = Double::for(SessionService::class);
    $sessions->expects('create')
        ->with(Argument::satisfies(fn (array $params): bool => $params['payment_method_types'] === ['card']), Argument::remaining())
        ->returns(fakeCateringStripeSession($inquiry, [
            'id' => 'cs_test_new',
            'status' => 'open',
            'payment_status' => 'unpaid',
            'url' => 'https://checkout.stripe.test/c/pay/cs_test_new',
        ]));
    bindCateringStripeSessions($sessions);

    $url = resolve(CateringDepositCheckoutService::class)->redirectToCheckout($inquiry, 250.00);

    expect($url)->toBe('https://checkout.stripe.test/c/pay/cs_test_new');
});
