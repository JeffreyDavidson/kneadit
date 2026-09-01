<?php

use App\Http\Controllers\Billing\SwapPlanController;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use JMac\Testing\Double;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('swap plan returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.swap', 'nonexistent-plan'))
        ->assertNotFound();
});

test('swap plan succeeds for valid plan with active subscription', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Double::for(Subscription::class);
    $subscription->expects('valid')->returns(true);
    $subscription->expects('swap')
        ->with('price_starter_test')
        ->returns($subscription);

    $user = Double::for(User::factory()->owner()->make())->passthru();
    $user->expects('subscription')
        ->with('default')
        ->returns($subscription);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('success'))->toBe('Your plan has been updated!');
});

test('swap plan shows error when stripe throws exception', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Double::for(Subscription::class);
    $subscription->expects('valid')->returns(true);
    $subscription->expects('swap')
        ->throws(new Exception('Stripe API error'));

    $user = Double::for(User::factory()->owner()->make())->passthru();
    $user->expects('subscription')
        ->with('default')
        ->returns($subscription);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('error'))->toContain('Unable to update your plan');
});

test('swap plan requires authentication', function () {
    $this->post(route('billing.swap', 'starter'))
        ->assertRedirect(route('login'));
});

test('swap plan handles null subscription gracefully', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = Double::for(User::factory()->owner()->make())->passthru();
    $user->expects('subscription')
        ->with('default')
        ->returns(null);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('error'))->toBe('No active subscription found. Choose a plan to start billing.');
});

test('swap plan rejects an ended subscription', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Double::for(Subscription::class);
    $subscription->expects('valid')->returns(false);

    $user = Double::for(User::factory()->owner()->make())->passthru();
    $user->expects('subscription')
        ->with('default')
        ->returns($subscription);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('error'))->toBe('No active subscription found. Choose a plan to start billing.');
});
