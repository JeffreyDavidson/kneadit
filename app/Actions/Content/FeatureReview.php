<?php

namespace App\Actions\Content;

use App\Models\Engagement\Review;

class FeatureReview
{
    public function __invoke(Review $review): void
    {
        $review->update(['is_featured' => true]);
    }
}
