<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Analytics;

use Carbon\Carbon;

final readonly class RecentReview
{
    public function __construct(
        public int $id,
        public string $customerName,
        public string $productName,
        public int $rating,
        public ?string $comment,
        public bool $isApproved,
        public bool $isFeatured,
        public ?Carbon $createdAt,
    ) {}

    /** @return array{id: int, customer_name: string, product_name: string, rating: int, comment: string|null, is_approved: bool, is_featured: bool, created_at: Carbon|null} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'product_name' => $this->productName,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->isApproved,
            'is_featured' => $this->isFeatured,
            'created_at' => $this->createdAt,
        ];
    }
}
