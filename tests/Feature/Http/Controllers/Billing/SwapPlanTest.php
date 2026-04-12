<?php

use App\Http\Controllers\Billing\SwapPlanController;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('swap plan returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post('/billing/swap/nonexistent-plan')
        ->assertNotFound();
});

test('swap plan succeeds for valid plan with active subscription', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Mockery::mock(Subscription::class);
    $subscription->shouldReceive('swap')
        ->once()
        ->with('price_starter_test')
        ->andReturnSelf();

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    $user->shouldReceive('subscription')
        ->with('default')
        ->andReturn($subscription);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('success'))->toBe('Your plan has been updated!');
});

test('swap plan shows error when stripe throws exception', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Mockery::mock(Subscription::class);
    $subscription->shouldReceive('swap')
        ->once()
        ->andThrow(new Exception('Stripe API error'));

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    $user->shouldReceive('subscription')
        ->with('default')
        ->andReturn($subscription);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('error'))->toContain('Unable to update your plan');
});

test('swap plan requires authentication', function () {
    $this->post('/billing/swap/starter')
        ->assertRedirect('/login');
});

test('swap plan handles null subscription gracefully', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    $user->shouldReceive('subscription')
        ->with('default')
        ->andReturn(null);

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    // null?->swap() is a no-op so success path
    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getSession()->get('success'))->toBe('Your plan has been updated!');
});
