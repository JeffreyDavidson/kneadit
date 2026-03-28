<?php

use App\Enums\OrderStatus;

test('OrderStatus has a label method', function () {
    expect(OrderStatus::Pending->label())->toBe('Pending')
        ->and(OrderStatus::Confirmed->label())->toBe('Confirmed')
        ->and(OrderStatus::Cancelled->label())->toBe('Cancelled');
});
