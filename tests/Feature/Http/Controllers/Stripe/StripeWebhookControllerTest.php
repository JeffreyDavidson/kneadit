<?php

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

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_test_' . uniqid(),
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

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $eventId = 'evt_duplicate_' . uniqid();
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

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleCustomerSubscriptionUpdated');

    $method->invoke($controller, [
        'id' => 'evt_subscription_update_' . uniqid(),
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
    $tenant->refresh();

    expect($tenant->plan)->toBe(SubscriptionTier::Growth);
})->group('launch-critical');

test('SyncSubscriptionPlan updates tenant plan from stripe price id', function () {
    $stripePrices = ['starter' => 'price_starter_id', 'growth' => 'price_growth_id'];
    config(['kneadit.stripe_prices' => $stripePrices]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_sync_test']);
    $tenant = Tenant::factory()->create(['email' => $user->email, 'plan' => SubscriptionTier::Starter]);

    $priceMap = array_flip($stripePrices);

    resolve(App\Actions\Stripe\SyncSubscriptionPlan::class)(
        tenantEmail: $user->email,
        stripePriceId: 'price_growth_id',
        priceMap: $priceMap,
    );

    $tenant->refresh();

    expect($tenant->plan)->toBe(SubscriptionTier::Growth);
});

test('SyncSubscriptionPlan does nothing for unknown price id', function () {
    $stripePrices = ['starter' => 'price_starter_id'];
    config(['kneadit.stripe_prices' => $stripePrices]);

    $user = User::factory()->owner()->create(['stripe_id' => 'cus_unknown_price']);
    $tenant = Tenant::factory()->create(['email' => $user->email, 'plan' => SubscriptionTier::Starter]);

    resolve(App\Actions\Stripe\SyncSubscriptionPlan::class)(
        tenantEmail: $user->email,
        stripePriceId: 'price_nonexistent',
        priceMap: array_flip($stripePrices),
    );

    $tenant->refresh();

    expect($tenant->plan)->toBe(SubscriptionTier::Starter);
});

test('handleCustomerSubscriptionDeleted calls parent and logs', function () {
    $source = file_get_contents(app_path('Http/Controllers/Stripe/StripeWebhookController.php'));

    expect($source)
        ->toContain('handleCustomerSubscriptionDeleted')
        ->toContain('parent::handleCustomerSubscriptionDeleted')
        ->toContain('subscription fully canceled')
        ->toContain('StripeCustomerLookupQuery::find');
});

test('handleInvoicePaymentFailed skips when no customer id in payload', function () {
    Event::fake([PaymentFailed::class]);

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_no_customer_' . uniqid(),
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

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_no_user_' . uniqid(),
        'data' => [
            'object' => [
                'customer' => 'cus_does_not_exist',
                'amount_due' => 1000,
            ],
        ],
    ]);

    Event::assertNotDispatched(PaymentFailed::class);
});

test('alreadyProcessed returns false for missing event id', function () {
    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'alreadyProcessed');

    expect($method->invoke($controller, []))->toBeFalse();
});
