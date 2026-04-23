<?php

namespace App\Events\Customers;

use App\Models\Engagement\Review;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class LowReviewReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Review $review,
    ) {}
}
