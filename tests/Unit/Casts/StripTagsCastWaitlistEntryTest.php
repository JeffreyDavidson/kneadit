<?php

use App\Casts\StripTagsCast;
use App\Models\Customers\WaitlistEntry;

it('has StripTagsCast on WaitlistEntry notes column', function () {
    $entry = new WaitlistEntry;
    $casts = $entry->getCasts();

    expect($casts['notes'])->toBe(StripTagsCast::class);
});
