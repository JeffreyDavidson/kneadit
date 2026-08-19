<?php

namespace App\DataTransferObjects\Analytics;

final readonly class ReviewRatingDistributionEntry
{
    public function __construct(
        public int $rating,
        public int $count,
        public float $percentage,
    ) {}

    /** @return array{rating: int, count: int, percentage: float} */
    public function toArray(): array
    {
        return [
            'rating' => $this->rating,
            'count' => $this->count,
            'percentage' => $this->percentage,
        ];
    }
}
