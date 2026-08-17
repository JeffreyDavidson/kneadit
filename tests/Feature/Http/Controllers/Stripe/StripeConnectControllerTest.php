<?php

use App\Actions\Stripe\InitiateStripeConnect;
use App\Http\Controllers\Stripe\StripeConnectController;
use Illuminate\Http\RedirectResponse;

test('stripe connect controller redirects to onboarding url', function () {
    $action = Mockery::mock(InitiateStripeConnect::class);
    mockExpectation($action, '__invoke')
        ->once()
        ->with(
            Mockery::on(fn ($url) => str_contains($url, 'stripe_connect=refresh')),
            Mockery::on(fn ($url) => str_contains($url, 'stripe_connect=complete')),
        )
        ->andReturn('https://connect.stripe.com/setup/test123');

    throw_unless($action instanceof InitiateStripeConnect, UnexpectedValueException::class, 'Expected a mocked Stripe Connect action.');

    $controller = new StripeConnectController;
    $response = $controller($action);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('https://connect.stripe.com/setup/test123');
});

test('stripe connect controller passes correct refresh and return urls', function () {
    $capturedRefresh = null;
    $capturedReturn = null;

    $action = Mockery::mock(InitiateStripeConnect::class);
    mockExpectation($action, '__invoke')
        ->once()
        ->andReturnUsing(function (string $refreshUrl, string $returnUrl) use (&$capturedRefresh, &$capturedReturn) {
            $capturedRefresh = $refreshUrl;
            $capturedReturn = $returnUrl;

            return 'https://connect.stripe.com/test';
        });

    throw_unless($action instanceof InitiateStripeConnect, UnexpectedValueException::class, 'Expected a mocked Stripe Connect action.');

    $controller = new StripeConnectController;
    $controller($action);

    expect($capturedRefresh)->toContain('/admin/onboarding?stripe_connect=refresh')
        ->and($capturedReturn)->toContain('/admin/onboarding?stripe_connect=complete');
});
