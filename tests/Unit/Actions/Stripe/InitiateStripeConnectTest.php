<?php

use App\Actions\Stripe\InitiateStripeConnect;

test('it can be instantiated', function () {
    expect(class_exists(InitiateStripeConnect::class))->toBeTrue();
});

test('it uses settings to resolve existing connect account', function () {
    $source = file_get_contents(app_path('Actions/Stripe/InitiateStripeConnect.php'));

    expect($source)
        ->toContain("'stripe_connect_id'")
        ->toContain('SettingsManager')
        ->toContain('accountLinks->create')
        ->toContain("'type' => 'account_onboarding'");
});

test('it creates a new connect account when none exists', function () {
    $source = file_get_contents(app_path('Actions/Stripe/InitiateStripeConnect.php'));

    expect($source)
        ->toContain('accounts->create')
        ->toContain("'type' => 'standard'")
        ->toContain("'country' => 'US'")
        ->toContain("->set('stripe_connect_id', \$account->id)");
});

test('it returns the account link url', function () {
    $source = file_get_contents(app_path('Actions/Stripe/InitiateStripeConnect.php'));

    expect($source)
        ->toContain('return $accountLink->url');
});

test('it accepts refresh and return urls', function () {
    $reflection = new ReflectionMethod(InitiateStripeConnect::class, '__invoke');
    $params = $reflection->getParameters();

    expect($params)->toHaveCount(2)
        ->and($params[0]->getName())->toBe('refreshUrl')
        ->and($params[1]->getName())->toBe('returnUrl');
});
