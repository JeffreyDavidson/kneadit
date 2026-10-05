<?php

declare(strict_types=1);

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Keeps secrets and personal values out of the activity log. A redacted key is
 * still recorded as changed, so the log shows that it happened without showing
 * what the value was.
 */
final class ActivityLogRedactor
{
    public const string REDACTED = '[redacted]';

    /** Keys that never go into the log, whichever model they belong to. */
    private const array SENSITIVE_KEYS = ['password', '*_token', '*_secret', 'two_factor_*'];

    /**
     * Redact the changes of one model: its hidden attributes and the denylisted keys.
     *
     * @param  array<array-key, mixed>  $changes
     * @return array<array-key, mixed>
     */
    public function redactChanges(Model $model, array $changes): array
    {
        return $this->redactSecrets($changes, $model->getHidden());
    }

    /**
     * Redact stored log properties (every nested array, such as `changes`).
     *
     * @param  array<array-key, mixed>  $properties
     * @param  array<string>  $hidden
     * @return array<array-key, mixed>
     */
    public function redactProperties(array $properties, array $hidden = []): array
    {
        return $this->redactSecrets($properties, $hidden);
    }

    /**
     * Redact the given keys wherever they appear in stored log properties.
     *
     * @param  array<array-key, mixed>  $properties
     * @param  list<string>  $keys
     * @return array<array-key, mixed>
     */
    public function redactKeys(array $properties, array $keys): array
    {
        $redacted = [];

        foreach ($properties as $key => $value) {
            $redacted[$key] = match (true) {
                in_array($key, $keys, true) => self::REDACTED,
                is_array($value) => $this->redactKeys($value, $keys),
                default => $value,
            };
        }

        return $redacted;
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @param  array<string>  $hidden
     * @return array<array-key, mixed>
     */
    private function redactSecrets(array $values, array $hidden): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            $redacted[$key] = match (true) {
                $this->isSensitive((string) $key, $hidden) => self::REDACTED,
                is_array($value) => $this->redactSecrets($value, $hidden),
                default => $value,
            };
        }

        return $redacted;
    }

    /** @param  array<string>  $hidden */
    private function isSensitive(string $key, array $hidden): bool
    {
        return in_array($key, $hidden, true) || Str::is(self::SENSITIVE_KEYS, $key);
    }
}
