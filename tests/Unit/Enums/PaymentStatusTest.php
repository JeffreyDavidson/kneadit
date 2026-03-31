<?php

use App\Enums\PaymentStatus;

test('PaymentStatus has a color for every case', function () {
    foreach (PaymentStatus::cases() as $case) {
        expect($case->getColor())->toBeString();
    }
});
