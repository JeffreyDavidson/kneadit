<?php

use App\Enums\Customers\CateringEventType;

test('CateringEventType has a color for every case', function (CateringEventType $case) {
    expect($case->getColor())->toBeString();
})->with(CateringEventType::cases());
