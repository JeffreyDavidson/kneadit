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

        return floatval($state);
    }

    public function set(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Percentage) {
            return $state->value();
        }

        return floatval($state);
    }
}
