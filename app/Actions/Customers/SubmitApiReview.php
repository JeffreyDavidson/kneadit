<?php

namespace App\Actions\Customers;

use App\Models\Engagement\Review;

class SubmitApiReview
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): Review
    {
        return Review::query()->create([
            ...$data,
            'is_approved' => false,
        ]);
    }
}
