<?php

use App\Filament\Pages\Onboarding;

beforeEach(fn () => setUpTenantTest());

test('payment step stores payment methods as json array', function () {
    $page = new Onboarding;
    $page->payment_methods = ['cash', 'paypal', 'stripe'];
    $page->paypal_client_id = 'test_id';
    $page->paypal_client_secret = 'test_secret';
    $page->paypal_sandbox = true;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    $stored = settings('payment_methods');
    expect($stored)->not->toBeNull();

    $decoded = json_decode($stored, true);
    expect($decoded)->toBeArray();
    expect($decoded)->toContain('cash');
    expect($decoded)->toContain('paypal');
    expect($decoded)->toContain('stripe');
});

test('payment step sets legacy payment method to first value', function () {
    $page = new Onboarding;
    $page->payment_methods = ['stripe', 'cash'];
    $page->paypal_client_id = '';
    $page->paypal_client_secret = '';
    $page->paypal_sandbox = false;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    expect(settings('payment_method'))->toBe('stripe');
});

test('payment step defaults to cash when empty', function () {
    $page = new Onboarding;
    $page->payment_methods = [];
    $page->paypal_client_id = '';
    $page->paypal_client_secret = '';
    $page->paypal_sandbox = false;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    expect(settings('payment_method'))->toBe('cash');
});

test('payment methods property defaults to cash', function () {
    $page = new Onboarding;

    expect($page->payment_methods)->toBe(['cash']);
});
