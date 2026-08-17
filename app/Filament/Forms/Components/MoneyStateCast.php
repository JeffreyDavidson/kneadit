<?php

namespace App\Filament\Forms\Components;

use App\ValueObjects\Money;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class MoneyStateCast implements StateCast
{
    public function get(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Money) {
            return $state->dollars();
        }

        return $this->numericValue($state);
    }

    public function set(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Money) {
            return $state->dollars();
        }

        return $this->numericValue($state);
    }

    private function numericValue(mixed $state): float
    {
        if (is_float($state) || is_int($state)) {
            return $state;
        }

        if (! is_string($state) || ! is_numeric($state)) {
            throw new \UnexpectedValueException('Money form state must be numeric.');
        }

        return (float) $state;
    }
}
