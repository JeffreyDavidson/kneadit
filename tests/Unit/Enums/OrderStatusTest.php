<?php

use App\Enums\OrderStatus;

test('OrderStatus has a label method', function () {
    expect(OrderStatus::Pending->getLabel())->toBe('Pending')
        ->and(OrderStatus::Confirmed->getLabel())->toBe('Confirmed')
        ->and(OrderStatus::Cancelled->getLabel())->toBe('Cancelled');
});
