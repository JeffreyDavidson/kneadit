<?php

use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

it('fetches and caches an access token', function () {
    config([
        'services.paypal.client_id' => 'test-id',
        'services.paypal.client_secret' => 'test-secret',
        'services.paypal.sandbox' => true,
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'test-token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]),
    ]);

    $manager = new TokenManager;
    $token = $manager->getAccessToken();

    expect($token)->toBe('test-token-123');

    // Second call should return cached token without another HTTP call
    $token2 = $manager->getAccessToken();
    expect($token2)->toBe('test-token-123');

    Http::assertSentCount(1);
});
