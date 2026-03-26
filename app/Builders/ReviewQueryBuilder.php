<?php

namespace App\Builders;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Review> */
class ReviewQueryBuilder extends Builder
{
    public function approved(): static
    {
        $this->where('is_approved', true);

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
}
