<?php

namespace App\DataTransferObjects\Analytics;

final readonly class ReviewMonthlyTrend
{
    public function __construct(
        public string $month,
        public string $monthKey,
        public int $count,
        public float $averageRating,
    ) {}

    /** @return array{month: string, month_key: string, count: int, avg_rating: float} */
    public function toArray(): array
    {
        return [
            'month' => $this->month,
            'month_key' => $this->monthKey,
            'count' => $this->count,
            'avg_rating' => $this->averageRating,
        ];
    }
}
