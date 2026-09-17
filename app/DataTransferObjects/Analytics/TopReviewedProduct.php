<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Analytics;

final readonly class TopReviewedProduct
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $reviewsCount,
        public float $averageRating,
    ) {}

    /** @return array{id: int, name: string, reviews_count: int|null, average_rating: float} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reviews_count' => $this->reviewsCount,
            'average_rating' => $this->averageRating,
        ];
    }
}
