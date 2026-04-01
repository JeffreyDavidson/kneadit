<?php

namespace App\Actions\Content;

use App\Models\Engagement\Review;

class ApproveReview
{
    public function __invoke(Review $review): void
    {
        $review->update(['is_approved' => true]);
    }
}
