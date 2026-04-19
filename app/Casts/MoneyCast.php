<?php

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<Money|null, Money|float|string|null> */
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::fromDollars((float) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): float|string|null
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->dollars();
        }

        return (float) $value;
    }
}
