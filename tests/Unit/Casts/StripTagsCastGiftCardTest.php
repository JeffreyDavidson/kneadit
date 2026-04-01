<?php

use App\Casts\StripTagsCast;
use App\Models\Financial\GiftCard;

it('has StripTagsCast on GiftCard message column', function () {
    $card = new GiftCard;
    $casts = $card->getCasts();

    expect($casts['message'])->toBe(StripTagsCast::class);
});
