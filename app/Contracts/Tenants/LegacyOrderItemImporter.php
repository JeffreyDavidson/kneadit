<?php

namespace App\Contracts\Tenants;

interface LegacyOrderItemImporter
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<int, int> $orderIds
     * @param array<int, int> $productIds
     */
    public function import(array $items, array $orderIds, array $productIds): void;
}
