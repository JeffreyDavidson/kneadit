<?php

use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

function paymentVerifierTokenManager(?string $accessToken): TokenManager
{
    return new class($accessToken) extends TokenManager {
        public function __construct(private ?string $accessToken) {}

        public function getAccessToken(): ?string
        {
            return $this->accessToken;
        }

        public function getBaseUrl(): string
        {
            return 'https://api-m.sandbox.paypal.com';
        }
    };
}

test('it returns invoice status on success', function () {
    $tokenManager = paymentVerifierTokenManager('test-token');

    Http::fake([
        'api-m.sandbox.paypal.com/v2/invoicing/invoices/INV-001' => Http::response([
            'status' => 'PAID',
        ], 200),
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBe('PAID');
});

test('it returns null when access token is unavailable', function () {
    $tokenManager = paymentVerifierTokenManager(null);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-001'))->toBeNull();
});

test('it returns null on api failure', function () {
    $tokenManager = paymentVerifierTokenManager('test-token');

    Http::fake([
        'api-m.sandbox.paypal.com/v2/invoicing/invoices/INV-002' => Http::response([
            'error' => 'Not found',
        ], 404),
    ]);

    $verifier = new PaymentVerifier($tokenManager);

    expect($verifier->getInvoiceStatus('INV-002'))->toBeNull();
});
