<?php

namespace App\Filament\Forms\Components;

use App\ValueObjects\Percentage;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class PercentageStateCast implements StateCast
{
    public function get(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Percentage) {
            return $state->value();
        }

        return $this->numericValue($state);
    }

    public function set(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Percentage) {
            return $state->value();
        }

        return $this->numericValue($state);
    }

    private function numericValue(mixed $state): float
    {
        if (is_float($state) || is_int($state)) {
            return $state;
        }

        if (! is_string($state) || ! is_numeric($state)) {
            throw new \UnexpectedValueException('Percentage form state must be numeric.');
        }

        return (float) $state;
    }
}
