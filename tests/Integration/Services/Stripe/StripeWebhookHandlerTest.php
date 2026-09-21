<?php

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Stripe\StripeWebhookController;
use Laravel\Cashier\Http\Controllers\WebhookController;

beforeEach(function () {
    setUpCentralTest();
});

test('webhook controller extends cashier', function () {
    expect(is_subclass_of(StripeWebhookController::class, WebhookController::class))->toBeTrue();
});

test('webhook controller handles subscription updated', function () {
    expect(method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionUpdated'))->toBeTrue();
});

test('webhook controller handles payment failed', function () {
    expect(method_exists(StripeWebhookController::class, 'handleInvoicePaymentFailed'))->toBeTrue();
});

test('webhook controller handles subscription deleted', function () {
    expect(method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionDeleted'))->toBeTrue();
});

test('price id to plan mapping uses SubscriptionTier::fromPriceId', function () {
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_test_starter',
        'growth' => 'price_test_growth',
        'pro' => 'price_test_pro',
    ]]);

    expect(SubscriptionTier::fromPriceId('price_test_starter'))->toBe(SubscriptionTier::Starter)
        ->and(SubscriptionTier::fromPriceId('price_test_growth'))->toBe(SubscriptionTier::Growth)
        ->and(SubscriptionTier::fromPriceId('price_test_pro'))->toBe(SubscriptionTier::Pro)
        ->and(SubscriptionTier::fromPriceId('price_unknown'))->toBeNull();
});

test('webhook route uses custom controller', function () {
    $source = file_get_contents(base_path('routes/billing.php'));

    expect($source)->toContain('StripeWebhookController')->not->toContain('Cashier\Http\Controllers\WebhookController');
});

test('payment failed handler dispatches PaymentFailed event', function () {
    $source = file_get_contents(app_path('Services/Stripe/StripeWebhookEventHandler.php'));

    expect($source)->toContain('Payment failed')->toContain('new PaymentFailed(');
});
