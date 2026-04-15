<?php

use App\Enums\Orders\PaymentStatus;

test('PaymentStatus has a color for every case', function (PaymentStatus $case) {
    expect($case->getColor())->toBeString();
})->with(PaymentStatus::cases());
