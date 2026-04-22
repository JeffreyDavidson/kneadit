<?php

namespace App\Builders\Customers;

use App\Models\Customers\CustomerPhoto;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CustomerPhoto> */
class CustomerPhotoQueryBuilder extends Builder
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

    public function withCaptionLike(string $term): static
    {
        $this->whereLike('caption', "%{$term}%");

        return $this;
    }
}
