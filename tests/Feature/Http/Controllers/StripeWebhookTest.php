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

    Event::assertDispatched(PaymentFailed::class, fn (PaymentFailed $event) => $event->user->email === $user->email
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
