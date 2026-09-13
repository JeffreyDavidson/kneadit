<?php

namespace App\Services\Tenants\Contracts;

interface LegacyOrderImporter
{
    /**
     * @param array<int, array<string, mixed>> $orders
     * @param array<int, array<string, mixed>> $orderNotes
     * @param array<string, int> $customerIds
     * @param array<int, int> $couponIds
     * @return array<int, int>
     */
    public function import(array $orders, array $orderNotes, array $customerIds, array $couponIds): array;
}
