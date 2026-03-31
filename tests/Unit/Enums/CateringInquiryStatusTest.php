<?php

use App\Enums\CateringInquiryStatus;

test('CateringInquiryStatus has a color for every case', function () {
    foreach (CateringInquiryStatus::cases() as $case) {
        expect($case->getColor())->toBeString();
    }
});
