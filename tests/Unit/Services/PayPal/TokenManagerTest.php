<?php

use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => setUpTenantTest());

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

    $manager = resolve(TokenManager::class);
    $token = $manager->getAccessToken();

    expect($token)->toBe('test-token-123');

    // Second call should return cached token without another HTTP call
    $token2 = $manager->getAccessToken();
    expect($token2)->toBe('test-token-123');

    Http::assertSentCount(1);
});

test('uses production URL when sandbox is false', function () {
    config([
        'services.paypal.sandbox' => false,
        'services.paypal.client_id' => 'test-id',
        'services.paypal.client_secret' => 'test-secret',
    ]);

    $manager = resolve(TokenManager::class);

    expect($manager->getBaseUrl())->toBe('https://api-m.paypal.com');
});

test('returns null when API response fails', function () {
    config([
        'services.paypal.sandbox' => true,
        'services.paypal.client_id' => 'test-id',
        'services.paypal.client_secret' => 'test-secret',
    ]);

    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $manager = resolve(TokenManager::class);

    expect($manager->getAccessToken())->toBeNull();
});

test('returns null when API throws exception', function () {
    config([
        'services.paypal.sandbox' => true,
        'services.paypal.client_id' => 'test-id',
        'services.paypal.client_secret' => 'test-secret',
    ]);

    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(fn () => throw new Exception('Connection refused')),
    ]);

    $manager = resolve(TokenManager::class);

    expect($manager->getAccessToken())->toBeNull();
});
