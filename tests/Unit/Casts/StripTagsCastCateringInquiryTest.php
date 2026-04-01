<?php

use App\Casts\StripTagsCast;
use App\Models\Customers\CateringInquiry;

it('has StripTagsCast on CateringInquiry details, dietary_requirements, and venue_address columns', function () {
    $inquiry = new CateringInquiry;
    $casts = $inquiry->getCasts();

    expect($casts['details'])->toBe(StripTagsCast::class)
        ->and($casts['dietary_requirements'])->toBe(StripTagsCast::class)
        ->and($casts['venue_address'])->toBe(StripTagsCast::class);
});
