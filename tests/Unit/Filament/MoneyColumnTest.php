<?php

use App\Filament\Tables\Columns\MoneyColumn;

test('money column instantiates with given name', function () {
    $column = MoneyColumn::make('total');

    expect($column->getName())->toBe('total');
});
