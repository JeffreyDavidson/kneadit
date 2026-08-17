<?php

use App\Services\Settings\TenantSettingCipher;
use Illuminate\Support\Facades\Crypt;

test('encrypts sensitive setting values only once', function () {
    $cipher = resolve(TenantSettingCipher::class);
    $encrypted = $cipher->encrypt('paypal_client_secret', 'secret-value');

    if (! is_string($encrypted)) {
        throw new UnexpectedValueException('Expected an encrypted setting string.');
    }

    expect($encrypted)
        ->not->toBe('secret-value')
        ->and(Crypt::decryptString($encrypted))->toBe('secret-value')
        ->and($cipher->encrypt('paypal_client_secret', $encrypted))->toBe($encrypted);
});

test('leaves ordinary settings unchanged', function () {
    $cipher = resolve(TenantSettingCipher::class);

    expect($cipher->encrypt('store_name', 'Sunrise Bakery'))->toBe('Sunrise Bakery')
        ->and($cipher->decrypt('store_name', 'Sunrise Bakery'))->toBe('Sunrise Bakery');
});

test('accepts legacy plaintext sensitive values while migrating', function () {
    expect(resolve(TenantSettingCipher::class)->decrypt('webhook_secret', 'legacy-secret'))
        ->toBe('legacy-secret');
});
