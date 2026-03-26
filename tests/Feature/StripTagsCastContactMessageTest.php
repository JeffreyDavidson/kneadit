<?php

use App\Casts\StripTagsCast;
use App\Models\ContactMessage;

it('has StripTagsCast on ContactMessage name, subject, and message columns', function () {
    $msg = new ContactMessage;
    $casts = $msg->getCasts();

    expect($casts['name'])->toBe(StripTagsCast::class)
        ->and($casts['subject'])->toBe(StripTagsCast::class)
        ->and($casts['message'])->toBe(StripTagsCast::class);
});
