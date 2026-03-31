<?php

use App\Enums\OrderStatus;

test('trackableStatuses returns statuses excluding cancelled', function () {
    $statuses = OrderStatus::trackableStatuses();

    expect($statuses)->toHaveCount(5)
        ->and($statuses)->not->toContain(OrderStatus::Cancelled);
});
