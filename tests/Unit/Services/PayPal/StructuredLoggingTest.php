<?php

use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    setUpTenantTest();
    config([
        'services.paypal.client_id' => 'test-id',
        'services.paypal.client_secret' => 'test-secret',
        'services.paypal.sandbox' => true,
    ]);
});

it('logs structured context on authentication exception', function () {
    $logger = Log::spy();

    Http::preventStrayRequests();
    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => function () {
            throw new Exception('DNS resolution failed');
        },
    ]);

    $manager = resolve(TokenManager::class);
    $result = $manager->getAccessToken();

    expect($result)->toBeNull();
    $verification = $logger->shouldHaveReceived('error');

    if (! $verification instanceof Mockery\VerificationDirector) {
        throw new RuntimeException('Expected a concrete Mockery verification.');
    }

    $verification->withArgs(
        fn ($message, $context) => $message === 'PayPal authentication error'
        && isset($context['error']),
    )
        ->once();
});

it('logs structured context on invoice status check exception', function () {
    $logger = Log::spy();

    Http::preventStrayRequests();
    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'test-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]),
        'api-m.sandbox.paypal.com/v2/invoicing/invoices/*' => function () {
            throw new Exception('Connection refused');
        },
    ]);

    $tokenManager = resolve(TokenManager::class);
    $verifier = new PaymentVerifier($tokenManager);
    $result = $verifier->getInvoiceStatus('INV-456');

    expect($result)->toBeNull();
    $verification = $logger->shouldHaveReceived('error');

    if (! $verification instanceof Mockery\VerificationDirector) {
        throw new RuntimeException('Expected a concrete Mockery verification.');
    }

    $verification->withArgs(
        fn ($message, $context) => $message === 'PayPal invoice status check error'
        && isset($context['invoice_id'])
        && isset($context['error']),
    )
        ->once();
});
