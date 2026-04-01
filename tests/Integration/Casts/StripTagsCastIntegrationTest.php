<?php

use App\Casts\StripTagsCast;
use App\Models\Engagement\Review;

it('has StripTagsCast on Review comment column', function () {
    $review = new Review;
    $casts = $review->getCasts();

    expect($casts['comment'])->toBe(StripTagsCast::class);
});
