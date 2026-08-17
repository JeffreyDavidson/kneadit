<?php

namespace App\Services\Settings;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use UnexpectedValueException;

class TenantSettingCipher
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'paypal_client_id',
        'paypal_client_secret',
        'webhook_secret',
    ];

    /** @return list<string> */
    public static function sensitiveKeys(): array
    {
        return self::SENSITIVE_KEYS;
    }

    public function encrypt(string $key, mixed $value): mixed
    {
        if (! in_array($key, self::SENSITIVE_KEYS, true) || $value === null) {
            return $value;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException("Sensitive setting {$key} must be a string or null.");
        }

        if ($this->isEncrypted($value)) {
            return $value;
        }

        return Crypt::encryptString($value);
    }

    public function decrypt(string $key, mixed $value): mixed
    {
        if (! in_array($key, self::SENSITIVE_KEYS, true) || ! is_string($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
}
