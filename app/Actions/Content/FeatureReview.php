<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Models\Engagement\Review;

class FeatureReview
{
    public function __invoke(Review $review): void
    {
        $review->update(['is_featured' => true]);
    }
}
