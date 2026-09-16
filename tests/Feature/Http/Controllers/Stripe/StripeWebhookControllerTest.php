<?php

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\PaymentFailed;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('handleInvoicePaymentFailed dispatches PaymentFailed event', function () {
    Event::fake([PaymentFailed::class]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_test123']);
    Tenant::factory()->create(['email' => $user->email]);

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_test_'.uniqid(),
        'data' => [
            'object' => [
                'customer' => 'cus_test123',
                'amount_due' => 2900,
            ],
        ],
    ]);

    Event::assertDispatched(fn (PaymentFailed $event) => $event->user->email === $user->email
        && $event->amount === 29.00);
});

test('duplicate events are skipped via idempotency check', function () {
    Event::fake([PaymentFailed::class]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_test456']);
    Tenant::factory()->create(['email' => $user->email]);

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $eventId = 'evt_duplicate_'.uniqid();
    $payload = [
        'id' => $eventId,
        'data' => ['object' => ['customer' => 'cus_test456', 'amount_due' => 1000]],
    ];

    $method->invoke($controller, $payload);
    Event::assertDispatched(PaymentFailed::class);

    Event::fake([PaymentFailed::class]);
    $method->invoke($controller, $payload);
    Event::assertNotDispatched(PaymentFailed::class);
});

test('failed webhook requests can retry and successful duplicates are acknowledged', function () {
    config(['cache.default' => 'array']);
    config(['cashier.webhook.secret' => 'whsec_test_secret']);
    config(['kneadit.stripe_prices' => ['growth' => 'price_retry_request']]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_retry_request']);
    $attempts = 0;
    $syncSubscriptionPlan = Mockery::mock(SyncSubscriptionPlan::class);
    $syncSubscriptionPlan->shouldReceive('__invoke')
        ->twice()
        ->andReturnUsing(function () use (&$attempts): void {
            $attempts++;

            if ($attempts === 1) {
                throw new RuntimeException('temporary event handling failure');
            }
        });
    app()->instance(SyncSubscriptionPlan::class, $syncSubscriptionPlan);

    $payload = json_encode([
        'id' => 'evt_retry_request',
        'type' => 'customer.subscription.updated',
        'data' => [
            'object' => [
                'id' => 'sub_retry_request',
                'customer' => $user->stripe_id,
                'status' => 'active',
                'metadata' => ['type' => 'default'],
                'items' => [
                    'data' => [[
                        'id' => 'si_retry_request',
                        'quantity' => 1,
                        'price' => [
                            'id' => 'price_retry_request',
                            'product' => 'prod_retry_request',
                        ],
                    ]],
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);
    $secret = 'whsec_test_secret';
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
    $headers = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ];

    $firstResponse = test()->call('POST', '/stripe/webhook', [], [], [], $headers, $payload);
    $retryResponse = test()->call('POST', '/stripe/webhook', [], [], [], $headers, $payload);
    $duplicateResponse = test()->call('POST', '/stripe/webhook', [], [], [], $headers, $payload);

    $firstResponse->assertServerError();
    $retryResponse->assertSuccessful();
    $duplicateResponse->assertSuccessful();
    expect($attempts)->toBe(2);
});

test('SubscriptionTier::fromPriceId maps stripe price to tier', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter', 'growth' => 'price_growth']]);

    expect(SubscriptionTier::fromPriceId('price_starter'))->toBe(SubscriptionTier::Starter)
        ->and(SubscriptionTier::fromPriceId('price_growth'))->toBe(SubscriptionTier::Growth)
        ->and(SubscriptionTier::fromPriceId('price_unknown'))->toBeNull();
});

test('subscription update webhook persists the subscription and syncs the tenant plan', function () {
    config(['kneadit.stripe_prices' => ['growth' => 'price_growth_webhook']]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_subscription_update']);
    $tenant = Tenant::factory()->create([
        'email' => $user->email,
        'plan' => SubscriptionTier::Starter,
    ]);

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleCustomerSubscriptionUpdated');

    $method->invoke($controller, [
        'id' => 'evt_subscription_update_'.uniqid(),
        'data' => [
            'object' => [
                'id' => 'sub_growth_webhook',
                'customer' => $user->stripe_id,
                'status' => 'active',
                'metadata' => ['type' => 'default'],
                'items' => [
                    'data' => [[
                        'id' => 'si_growth_webhook',
                        'quantity' => 1,
                        'price' => [
                            'id' => 'price_growth_webhook',
                            'product' => 'prod_growth_webhook',
                        ],
                    ]],
                ],
            ],
        ],
    ]);

    test()->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'stripe_id' => 'sub_growth_webhook',
        'stripe_price' => 'price_growth_webhook',
        'stripe_status' => 'active',
    ]);
    expect($tenant->fresh()->plan)->toBe(SubscriptionTier::Growth);
})->group('launch-critical');

test('SyncSubscriptionPlan updates tenant plan from stripe price id', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_id', 'growth' => 'price_growth_id']]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_sync_test']);
    $tenant = Tenant::factory()->create(['email' => $user->email, 'plan' => SubscriptionTier::Starter]);

    $priceMap = array_flip(config('kneadit.stripe_prices'));

    resolve(SyncSubscriptionPlan::class)(
        tenantEmail: $user->email,
        stripePriceId: 'price_growth_id',
        priceMap: $priceMap,
    );

    expect($tenant->fresh()->plan)->toBe(SubscriptionTier::Growth);
});

test('SyncSubscriptionPlan does nothing for unknown price id', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_id']]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_unknown_price']);
    $tenant = Tenant::factory()->create(['email' => $user->email, 'plan' => SubscriptionTier::Starter]);

    resolve(SyncSubscriptionPlan::class)(
        tenantEmail: $user->email,
        stripePriceId: 'price_nonexistent',
        priceMap: array_flip(config('kneadit.stripe_prices')),
    );

    expect($tenant->fresh()->plan)->toBe(SubscriptionTier::Starter);
});

test('handleCustomerSubscriptionDeleted calls parent and logs', function () {
    $source = file_get_contents(app_path('Http/Controllers/Stripe/StripeWebhookController.php'));

    expect($source)
        ->toContain('handleCustomerSubscriptionDeleted')
        ->toContain('parent::handleCustomerSubscriptionDeleted')
        ->toContain('handleSubscriptionDeleted');
});

test('handleInvoicePaymentFailed skips when no customer id in payload', function () {
    Event::fake([PaymentFailed::class]);

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_no_customer_'.uniqid(),
        'data' => [
            'object' => [
                'customer' => null,
                'amount_due' => 1000,
            ],
        ],
    ]);

    Event::assertNotDispatched(PaymentFailed::class);
});

test('handleInvoicePaymentFailed skips when user not found for customer', function () {
    Event::fake([PaymentFailed::class]);

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_no_user_'.uniqid(),
        'data' => [
            'object' => [
                'customer' => 'cus_does_not_exist',
                'amount_due' => 1000,
            ],
        ],
    ]);

    Event::assertNotDispatched(PaymentFailed::class);
});
