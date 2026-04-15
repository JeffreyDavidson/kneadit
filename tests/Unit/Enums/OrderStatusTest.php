<?php

use App\Enums\Orders\OrderStatus;

test('OrderStatus has a label method', function () {
    expect(OrderStatus::Pending->getLabel())->toBe('Pending')
        ->and(OrderStatus::Confirmed->getLabel())->toBe('Confirmed')
        ->and(OrderStatus::Cancelled->getLabel())->toBe('Cancelled');
});

test('OrderStatus has a color for every case', function (OrderStatus $case) {
    expect($case->getColor())->toBeString();
})->with(OrderStatus::cases());

test('OrderStatus has an icon for every case', function (OrderStatus $case) {
    expect($case->getIcon())->toBeInstanceOf(Filament\Support\Icons\Heroicon::class);
})->with(OrderStatus::cases());
