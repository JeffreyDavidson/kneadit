<?php

use App\Actions\Stripe\InitiateStripeConnect;
use App\Http\Controllers\Stripe\StripeConnectController;
use Illuminate\Http\RedirectResponse;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

test('stripe connect controller redirects to onboarding url', function () {
    $action = Double::for(InitiateStripeConnect::class);
    $action->expects('__invoke')
        ->with(
            Argument::satisfies(fn (mixed $url): bool => is_string($url) && str_contains($url, 'stripe_connect=refresh')),
            Argument::satisfies(fn (mixed $url): bool => is_string($url) && str_contains($url, 'stripe_connect=complete')),
        )
        ->returns('https://connect.stripe.com/setup/test123');

    $controller = new StripeConnectController;
    $response = $controller($action);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('https://connect.stripe.com/setup/test123');
});

test('stripe connect controller passes correct refresh and return urls', function () {
    $capturedRefresh = null;
    $capturedReturn = null;

    $action = Double::for(InitiateStripeConnect::class);
    $action->expects('__invoke')
        ->resolves(function (string $refreshUrl, string $returnUrl) use (&$capturedRefresh, &$capturedReturn): string {
            $capturedRefresh = $refreshUrl;
            $capturedReturn = $returnUrl;

            return 'https://connect.stripe.com/test';
        });

    $controller = new StripeConnectController;
    $controller($action);

    expect($capturedRefresh)->toContain('/admin/onboarding?stripe_connect=refresh')
        ->and($capturedReturn)->toContain('/admin/onboarding?stripe_connect=complete');
});
