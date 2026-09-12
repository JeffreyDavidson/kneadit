<?php

namespace App\Contracts\Tenants;

interface LegacyReviewImporter
{
    /**
     * @param array<int, array<string, mixed>> $reviews
     * @param array<int, int> $productIds
     * @param array<int, int> $orderIds
     */
    public function import(array $reviews, array $productIds, array $orderIds): void;
}
