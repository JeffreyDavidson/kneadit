<?php

use App\Enums\Marketing\EmailDeliveryStatus;

test('EmailDeliveryStatus is a string-backed enum with sent case', function () {
    expect(EmailDeliveryStatus::Sent->value)->toBe('sent')
        ->and(EmailDeliveryStatus::Sent->getLabel())->toBeString();
});
