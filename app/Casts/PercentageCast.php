<?php

namespace App\Casts;

use App\ValueObjects\Percentage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @implements CastsAttributes<Percentage|null, Percentage|float|int|string|null> */
class PercentageCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Percentage
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) && ! is_float($value) && ! is_string($value)) {
            throw new InvalidArgumentException("{$key} must contain a numeric percentage.");
        }

        return Percentage::fromFloat((float) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): float|int|string|null
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Percentage) {
            return $value->value();
        }

        return (float) $value;
    }
}
