<?php

use App\Http\Controllers\Billing\CheckoutController;
use App\Models\Staff\User;
use JMac\Testing\Double;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('checkout returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'nonexistent-plan'))
        ->assertNotFound();
});

test('checkout returns 404 for valid tier but no configured price', function () {
    config(['kneadit.stripe_prices' => ['starter' => null]]);

    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'starter'))
        ->assertNotFound();
});

test('checkout requires authentication', function () {
    $this->post(route('billing.checkout', 'starter'))
        ->assertRedirect(route('login'));
});

test('checkout refuses to start a second subscription', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_starter_test')
        ->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'starter'))
        ->assertRedirect(route('billing.plans'))
        ->assertSessionHas('error', 'You already have a subscription. Use Switch to change plans.');

    expect($user->subscriptions()->count())->toBe(1);
});

test('checkout gives a first-time subscriber the trial', function () {
    config([
        'kneadit.stripe_prices' => ['starter' => 'price_starter_test'],
        'kneadit.trial_days' => 14,
    ]);

    $user = User::factory()->owner()->create();

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialDays')->with(14)->returns($builder);
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = Double::for($user)->passthru();
    $subject->expects('newSubscription')->with('default', 'price_starter_test')->returns($builder);

    app(CheckoutController::class)($subject, 'starter');
});

test('checkout gives no trial to a returning subscriber whose subscription ended', function () {
    config([
        'kneadit.stripe_prices' => ['starter' => 'price_starter_test'],
        'kneadit.trial_days' => 14,
    ]);

    $user = User::factory()->owner()->create();
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_starter_test')
        ->state(['stripe_status' => 'canceled', 'ends_at' => now()->subMonth()])
        ->create();

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialDays')->never();
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = Double::for($user)->passthru();
    $subject->expects('newSubscription')->with('default', 'price_starter_test')->returns($builder);

    app(CheckoutController::class)($subject, 'starter');
});
