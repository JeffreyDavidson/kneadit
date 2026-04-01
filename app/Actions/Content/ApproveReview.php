<?php

namespace App\Actions\Content;

use App\Models\Review;

class ApproveReview
{
    public function __invoke(Review $review): void
    {
        $review->update(['is_approved' => true]);
    }
}
