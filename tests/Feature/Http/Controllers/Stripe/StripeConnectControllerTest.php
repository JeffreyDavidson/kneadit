<?php

use App\Actions\Stripe\InitiateStripeConnect;
use App\Http\Controllers\Stripe\StripeConnectController;
use Illuminate\Http\RedirectResponse;

test('stripe connect controller redirects to onboarding url', function () {
    $action = new class extends InitiateStripeConnect {
        public function __construct() {}

        public function __invoke(string $refreshUrl, string $returnUrl): string
        {
            expect($refreshUrl)->toContain('stripe_connect=refresh')
                ->and($returnUrl)->toContain('stripe_connect=complete');

            return 'https://connect.stripe.com/setup/test123';
        }
    };

    $controller = new StripeConnectController;
    $response = $controller($action);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('https://connect.stripe.com/setup/test123');
});

test('stripe connect controller passes correct refresh and return urls', function () {
    $action = new class extends InitiateStripeConnect {
        public ?string $capturedRefresh = null;

        public ?string $capturedReturn = null;

        public function __construct() {}

        public function __invoke(string $refreshUrl, string $returnUrl): string
        {
            $this->capturedRefresh = $refreshUrl;
            $this->capturedReturn = $returnUrl;

            return 'https://connect.stripe.com/test';
        }
    };

    $controller = new StripeConnectController;
    $controller($action);

    expect($action->capturedRefresh)->toContain('/admin/onboarding?stripe_connect=refresh')
        ->and($action->capturedReturn)->toContain('/admin/onboarding?stripe_connect=complete');
});
