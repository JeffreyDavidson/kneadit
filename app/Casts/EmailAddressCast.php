<?php

namespace App\Casts;

use App\Support\EmailAddress;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Stores a customer email lowercased and trimmed so lookups by email match
 * regardless of the case the customer typed.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EmailAddressCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException("{$key} must contain an email address string.");
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return EmailAddress::normalize($value);
    }
}
