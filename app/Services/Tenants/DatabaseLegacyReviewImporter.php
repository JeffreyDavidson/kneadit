<?php

namespace App\Services\Tenants;

use App\Services\Tenants\Contracts\LegacyReviewImporter;
use Illuminate\Support\Facades\DB;

class DatabaseLegacyReviewImporter implements LegacyReviewImporter
{
    /** @param array<int, array<string, mixed>> $reviews @param array<int, int> $productIds @param array<int, int> $orderIds */
    public function import(array $reviews, array $productIds, array $orderIds): void
    {
        foreach ($reviews as $review) {
            $email = $review['email'] ?: 'legacy-review-' . $this->stringValue($review['id']) . '@migration.invalid';
            DB::table('reviews')->updateOrInsert(
                ['customer_email' => $email, 'comment' => $review['body']],
                [
                    'customer_name' => $review['name'],
                    'product_id' => isset($review['product_id']) ? ($productIds[$this->integer($review['product_id'])] ?? null) : null,
                    'order_id' => isset($review['order_id']) ? ($orderIds[$this->integer($review['order_id'])] ?? null) : null,
                    'rating' => $review['rating'], 'is_approved' => ($review['status'] ?? null) === 'approved',
                    'is_featured' => $review['is_featured'] ?? false,
                    'created_at' => $review['created_at'] ?? now(), 'updated_at' => $review['updated_at'] ?? now(),
                ],
            );
        }
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }
}
