<?php

use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

test('returns null when token manager has no access token', function () {
    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturnNull();

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});

test('returns status when API responds successfully', function () {
    Http::fake([
        '*/v2/invoicing/invoices/INV-001' => Http::response(['status' => 'PAID']),
    ]);

    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->once()->andReturn('https://api-m.sandbox.paypal.com');

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBe('PAID');
});

test('returns null and logs error when API fails', function () {
    Http::fake([
        '*/v2/invoicing/invoices/INV-001' => Http::response(['error' => 'not found'], 404),
    ]);

    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->once()->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->once()->andReturn('https://api-m.sandbox.paypal.com');

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});
