<?php

namespace App\Filament\Forms\Components;

use App\ValueObjects\Money;
use Filament\Forms\Components\TextInput;

class MoneyInput extends TextInput
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->numeric()
            ->prefix('$')
            ->step(0.01)
            ->formatStateUsing(fn (mixed $state): mixed => $state instanceof Money ? $state->dollars() : $state);
    }
}
