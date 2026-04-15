<?php

use App\Actions\Stripe\ReauthenticateFromCheckoutSession;

test('action class exists and is invokable', function () {
    expect(ReauthenticateFromCheckoutSession::class)
        ->toHaveMethod('__invoke');
});

test('checks session status, customer, and timestamp before login', function () {
    $source = file_get_contents(app_path('Actions/Stripe/ReauthenticateFromCheckoutSession.php'));

    expect($source)
        ->toContain("status !== 'complete'")
        ->toContain('customer')
        ->toContain('subMinutes(30)')
        ->toContain('Auth::login');
});

test('retrieves checkout session via Cashier stripe client', function () {
    $source = file_get_contents(app_path('Actions/Stripe/ReauthenticateFromCheckoutSession.php'));

    expect($source)
        ->toContain('Cashier::stripe()->checkout->sessions->retrieve');
});
