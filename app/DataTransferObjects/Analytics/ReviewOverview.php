<?php

namespace App\DataTransferObjects\Analytics;

final readonly class ReviewOverview
{
    public function __construct(
        public int $totalReviews,
        public float $averageRating,
        public float $approvalRate,
        public int $approvedReviews,
    ) {}

    /** @return array{total_reviews: int, average_rating: float, approval_rate: float, approved_reviews: int} */
    public function toArray(): array
    {
        return [
            'total_reviews' => $this->totalReviews,
            'average_rating' => $this->averageRating,
            'approval_rate' => $this->approvalRate,
            'approved_reviews' => $this->approvedReviews,
        ];
    }
}
