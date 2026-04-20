<?php

namespace App\Casts;

use App\ValueObjects\Percentage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<Percentage|null, Percentage|float|int|string|null> */
class PercentageCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Percentage
    {
        if ($value === null) {
            return null;
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
