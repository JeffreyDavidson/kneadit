<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class MoneyInput extends TextInput
{
    public static function make(?string $name = null): static
    {
        $static = parent::make($name);

        $static->prefix('$');
        $static->step(0.01);
        $static->rule('numeric');
        $static->inputMode('decimal');

        return $static;
    }

    /** @return array<int, StateCast> */
    public function getDefaultStateCasts(): array
    {
        return [
            ...parent::getDefaultStateCasts(),
            app(MoneyStateCast::class),
        ];
    }
}
