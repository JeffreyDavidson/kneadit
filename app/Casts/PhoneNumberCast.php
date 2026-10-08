<?php

namespace App\Casts;

use App\Support\PhoneNumber;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Stores phone numbers in E.164 (`+19133877359`). A value that can't be read
 * as a phone number is stored as typed (trimmed) rather than thrown away.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class PhoneNumberCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException("{$key} must contain a phone number string.");
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return PhoneNumber::normalize($value);
    }
}
