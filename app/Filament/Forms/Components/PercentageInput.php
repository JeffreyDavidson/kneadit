<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class PercentageInput extends TextInput
{
    public static function make(?string $name = null): static
    {
        $static = parent::make($name);

        $static->suffix('%');
        $static->rule('numeric');
        $static->minValue(0);
        $static->maxValue(100);
        $static->inputMode('decimal');

        return $static;
    }

    /** @return array<int, StateCast> */
    public function getDefaultStateCasts(): array
    {
        return [
            ...parent::getDefaultStateCasts(),
            resolve(PercentageStateCast::class),
        ];
    }
}
