<?php

namespace App\Filament\Tables\Columns;

use App\ValueObjects\Money;
use Filament\Tables\Columns\TextColumn;

class MoneyColumn extends TextColumn
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->formatStateUsing(fn (mixed $state): ?string => self::formatMoney($state));
    }

    private static function formatMoney(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        if ($state instanceof Money) {
            return $state->formatted();
        }

        if (! is_string($state) && ! is_int($state) && ! is_float($state)) {
            throw new \UnexpectedValueException('Money column state must be numeric.');
        }

        if (! is_numeric($state)) {
            throw new \UnexpectedValueException('Money column state must be numeric.');
        }

        return Money::fromDollars((float) $state)->formatted();
    }
}
