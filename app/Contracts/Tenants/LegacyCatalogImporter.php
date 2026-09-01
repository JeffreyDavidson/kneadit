<?php

namespace App\Contracts\Tenants;

interface LegacyCatalogImporter
{
    /**
     * Import legacy catalog records and return their legacy-to-current ID maps.
     *
     * @param array<int, array<string, mixed>> $categories
     * @param array<int, array<string, mixed>> $products
     * @return array{category_ids: array<int, int>, product_ids: array<int, int>}
     */
    public function import(array $categories, array $products): array;
}
