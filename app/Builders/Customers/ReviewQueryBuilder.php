<?php

namespace App\Builders\Customers;

use App\Models\Engagement\Review;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Review> */
class ReviewQueryBuilder extends Builder
{
    public function approved(): static
    {
        $this->where('is_approved', true);

        return $this;
    }

    public function featured(): static
    {
        $this->where('is_featured', true);

        return $this;
    }

    public function withProduct(): static
    {
        $this->with('product');

        return $this;
    }

    public function withComments(): static
    {
        $this->whereNotNull('comment')->where('comment', '!=', '');

        return $this;
    }

    public function forDisplay(): static
    {
        $this->approved()->with('product')->latest();

        return $this;
    }

    /**
     * Get aggregate statistics for approved reviews.
     *
     * @return object{avg_rating: float, total_count: int}
     */
    public function statistics(): object
    {
        return $this->approved()
            ->toBase()
            ->selectRaw('COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_count')
            ->first();
    }
}
