<?php

use App\Enums\Customers\CateringEventType;

test('CateringEventType has a color for every case', function () {
    foreach (CateringEventType::cases() as $case) {
        expect($case->getColor())->toBeString();
    }
});
