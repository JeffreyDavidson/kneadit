<?php

use App\Services\PayPal\TokenManager;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => setUpTenantTest());

function bakeryPaypal(bool $sandbox = true): void
{
    settings([
        'paypal_client_id' => 'bakery-id',
        'paypal_client_secret' => 'bakery-secret',
        'paypal_sandbox' => $sandbox ? '1' : '0',
    ]);
}

it('fetches and caches an access token', function () {
    bakeryPaypal();

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

test('uses production URL when the bakery turns sandbox off', function () {
    bakeryPaypal(sandbox: false);

    $manager = resolve(TokenManager::class);

    expect($manager->getBaseUrl())->toBe('https://api-m.paypal.com');
});

test('uses the sandbox URL when the bakery turns sandbox on, whatever the platform config says', function () {
    config(['services.paypal.sandbox' => false]);
    bakeryPaypal(sandbox: true);

    expect(resolve(TokenManager::class)->getBaseUrl())->toBe('https://api-m.sandbox.paypal.com');
});

test('defaults to the sandbox when the bakery has never saved the setting', function () {
    settings(['paypal_client_id' => 'bakery-id', 'paypal_client_secret' => 'bakery-secret']);

    expect(resolve(TokenManager::class)->getBaseUrl())->toBe('https://api-m.sandbox.paypal.com');
});

test('does not fall back to the platform credentials outside the local environment', function () {
    config([
        'services.paypal.client_id' => 'platform-id',
        'services.paypal.client_secret' => 'platform-secret',
    ]);
    Http::preventStrayRequests();

    $manager = resolve(TokenManager::class);

    expect($manager->isConfigured())->toBeFalse()
        ->and($manager->getAccessToken())->toBeNull();
    Http::assertNothingSent();
});

test('reports a bakery with its own credentials as configured', function () {
    bakeryPaypal();

    expect(resolve(TokenManager::class)->isConfigured())->toBeTrue();
});

test('falls back to the platform credentials in the local environment only', function () {
    app()->detectEnvironment(fn (): string => 'local');
    config([
        'services.paypal.client_id' => 'platform-id',
        'services.paypal.client_secret' => 'platform-secret',
    ]);

    expect(resolve(TokenManager::class)->isConfigured())->toBeTrue();
});

test('returns null when API response fails', function () {
    bakeryPaypal();

    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $manager = resolve(TokenManager::class);

    expect($manager->getAccessToken())->toBeNull();
});

test('returns null when API throws exception', function () {
    bakeryPaypal();

    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => fn () => throw new Exception('Connection refused'),
    ]);

    $manager = resolve(TokenManager::class);

    expect($manager->getAccessToken())->toBeNull();
});
