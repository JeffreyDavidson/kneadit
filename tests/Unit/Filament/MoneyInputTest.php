<?php

use App\Filament\Forms\Components\MoneyInput;

test('money input is configured with currency prefix and 0.01 step', function () {
    $input = MoneyInput::make('amount');

    expect($input->getName())->toBe('amount')
        ->and($input->getPrefixLabel())->toBe('$')
        ->and((float) $input->getStep())->toBe(0.01)
        ->and($input->getInputMode())->toBe('decimal');
});
