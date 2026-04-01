<?php

use App\Enums\Customers\CateringInquiryStatus;

test('CateringInquiryStatus has a color for every case', function () {
    foreach (CateringInquiryStatus::cases() as $case) {
        expect($case->getColor())->toBeString();
    }
});
