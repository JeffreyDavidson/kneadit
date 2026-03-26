<?php

use App\Casts\StripTagsCast;
use App\Models\CustomerPhoto;

it('has StripTagsCast on CustomerPhoto customer_name and caption columns', function () {
    $photo = new CustomerPhoto;
    $casts = $photo->getCasts();

    expect($casts['customer_name'])->toBe(StripTagsCast::class)
        ->and($casts['caption'])->toBe(StripTagsCast::class);
});
