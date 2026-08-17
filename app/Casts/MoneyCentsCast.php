<?php

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Cast for money columns stored as integer cents (bigint).
 *
 * The legacy MoneyCast reads/writes decimal dollars, which carries float-
 * arithmetic ambiguity and inconsistency across the schema (some columns
 * are decimal(8,2), others (10,2)). Columns migrated to bigint cents use
 * this cast instead. The Money VO is already cents-native internally, so
 * this just removes the dollar→cent conversion at the cast boundary.
 *
 * @implements CastsAttributes<Money|null, Money|int|float|string|null>
 */
class MoneyCentsCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) && ! is_string($value)) {
            throw new InvalidArgumentException("{$key} must contain integer cents.");
        }

        return Money::fromCents((int) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->cents();
        }

        // Backward-compat: factories and seeders sometimes pass dollar floats
        // (e.g. 25.50). Treat any non-Money input as dollars and convert.
        return Money::fromDollars((float) $value)->cents();
    }
}
