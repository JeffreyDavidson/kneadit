<?php

use App\Http\Controllers\Billing\SwapPlanController;
use App\Models\Staff\User;
use Illuminate\Session\Store;
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

    $subscription = Mockery::mock(Subscription::class);
    mockExpectation($subscription, 'swap')
        ->once()
        ->with('price_starter_test')
        ->andReturnSelf();

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    mockExpectation($user, 'subscription')
        ->with('default')
        ->andReturn($subscription);

    throw_unless($user instanceof User, UnexpectedValueException::class, 'Expected a mocked billing user.');

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    $session = $response->getSession();

    throw_unless($session instanceof Store, UnexpectedValueException::class, 'Expected a redirect session.');

    expect($session->get('success'))->toBe('Your plan has been updated!');
});

test('swap plan shows error when stripe throws exception', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $subscription = Mockery::mock(Subscription::class);
    mockExpectation($subscription, 'swap')
        ->once()
        ->andThrow(new Exception('Stripe API error'));

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    mockExpectation($user, 'subscription')
        ->with('default')
        ->andReturn($subscription);

    throw_unless($user instanceof User, UnexpectedValueException::class, 'Expected a mocked billing user.');

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    $session = $response->getSession();

    throw_unless($session instanceof Store, UnexpectedValueException::class, 'Expected a redirect session.');

    expect($session->get('error'))->toContain('Unable to update your plan');
});

test('swap plan requires authentication', function () {
    $this->post(route('billing.swap', 'starter'))
        ->assertRedirect(route('login'));
});

test('swap plan handles null subscription gracefully', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = Mockery::mock(User::factory()->owner()->make())->makePartial();
    mockExpectation($user, 'subscription')
        ->with('default')
        ->andReturn(null);

    throw_unless($user instanceof User, UnexpectedValueException::class, 'Expected a mocked billing user.');

    $controller = new SwapPlanController;
    $response = $controller($user, 'starter');

    // null?->swap() is a no-op so success path
    $session = $response->getSession();

    throw_unless($session instanceof Store, UnexpectedValueException::class, 'Expected a redirect session.');

    expect($session->get('success'))->toBe('Your plan has been updated!');
});
