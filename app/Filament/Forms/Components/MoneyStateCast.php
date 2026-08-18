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

        return floatval($state);
    }

    public function set(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if ($state instanceof Money) {
            return $state->dollars();
        }

        return floatval($state);
    }
}
