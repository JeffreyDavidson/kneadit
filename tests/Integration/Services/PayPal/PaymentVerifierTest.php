<?php

use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

test('it returns invoice status on success', function () {
    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->once()->andReturn('https://api-m.sandbox.paypal.com');

    Http::fake([
        'api-m.sandbox.paypal.com/v2/invoicing/invoices/INV-001' => Http::response([
            'status' => 'PAID',
        ], 200),
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBe('PAID');
});

test('it returns null when access token is unavailable', function () {
    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturnNull();

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});

test('it returns null on api failure', function () {
    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->once()->andReturn('https://api-m.sandbox.paypal.com');

    Http::fake([
        'api-m.sandbox.paypal.com/v2/invoicing/invoices/INV-002' => Http::response([
            'error' => 'Not found',
        ], 404),
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-002'))->toBeNull();
});
