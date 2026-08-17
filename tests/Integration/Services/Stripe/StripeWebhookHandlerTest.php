<?php

use App\Http\Controllers\Stripe\StripeWebhookController;
use Laravel\Cashier\Http\Controllers\WebhookController;

beforeEach(function () {
    setUpCentralTest();
});

test('webhook controller extends cashier', function () {
    expect((new ReflectionClass(StripeWebhookController::class))->isSubclassOf(WebhookController::class))->toBeTrue();
});

test('webhook controller handles subscription updated', function () {
    expect((new ReflectionClass(StripeWebhookController::class))->hasMethod('handleCustomerSubscriptionUpdated'))->toBeTrue();
});

test('webhook controller handles payment failed', function () {
    expect((new ReflectionClass(StripeWebhookController::class))->hasMethod('handleInvoicePaymentFailed'))->toBeTrue();
});

test('webhook controller handles subscription deleted', function () {
    expect((new ReflectionClass(StripeWebhookController::class))->hasMethod('handleCustomerSubscriptionDeleted'))->toBeTrue();
});

test('price id to plan mapping uses SubscriptionTier::fromPriceId', function () {
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_test_starter',
        'growth' => 'price_test_growth',
        'pro' => 'price_test_pro',
    ]]);

    expect(App\Enums\Platform\SubscriptionTier::fromPriceId('price_test_starter'))->toBe(App\Enums\Platform\SubscriptionTier::Starter)
        ->and(App\Enums\Platform\SubscriptionTier::fromPriceId('price_test_growth'))->toBe(App\Enums\Platform\SubscriptionTier::Growth)
        ->and(App\Enums\Platform\SubscriptionTier::fromPriceId('price_test_pro'))->toBe(App\Enums\Platform\SubscriptionTier::Pro)
        ->and(App\Enums\Platform\SubscriptionTier::fromPriceId('price_unknown'))->toBeNull();
});

test('webhook route uses custom controller', function () {
    $source = file_get_contents(base_path('routes/billing.php'));

    expect($source)->toContain('StripeWebhookController')->not->toContain('Cashier\Http\Controllers\WebhookController');
});

test('payment failed handler dispatches PaymentFailed event', function () {
    $source = file_get_contents(app_path('Http/Controllers/Stripe/StripeWebhookController.php'));

    expect($source)->toContain('Payment failed')->toContain('new PaymentFailed(');
});
