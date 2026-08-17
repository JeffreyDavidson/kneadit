<?php

use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

test('returns null when token manager has no access token', function () {
    $tokenManager = Tests\Support\TypedMock::make(TokenManager::class);
    $tokenManager->allows(['getAccessToken' => null]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});

test('returns status when API responds successfully', function () {
    Http::fake([
        '*/v2/invoicing/invoices/INV-001' => Http::response(['status' => 'PAID']),
    ]);

    $tokenManager = Tests\Support\TypedMock::make(TokenManager::class);
    $tokenManager->allows([
        'getAccessToken' => 'test-token',
        'getBaseUrl' => 'https://api-m.sandbox.paypal.com',
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBe('PAID');
});

test('returns null and logs error when API fails', function () {
    Http::fake([
        '*/v2/invoicing/invoices/INV-001' => Http::response(['error' => 'not found'], 404),
    ]);

    $tokenManager = Tests\Support\TypedMock::make(TokenManager::class);
    $tokenManager->allows([
        'getAccessToken' => 'test-token',
        'getBaseUrl' => 'https://api-m.sandbox.paypal.com',
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});
