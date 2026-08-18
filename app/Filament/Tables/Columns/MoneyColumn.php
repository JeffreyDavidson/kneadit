<?php

namespace App\Filament\Tables\Columns;

use App\ValueObjects\Money;
use Filament\Tables\Columns\TextColumn;

class MoneyColumn extends TextColumn
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->formatStateUsing(fn (mixed $state): ?string => match (true) {
                $state === null => null,
                $state instanceof Money => $state->formatted(),
                default => Money::fromDollars((float) $state)->formatted(),
            });
    }
}
