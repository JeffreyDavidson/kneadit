<?php

namespace App\Services\Tenants\Contracts;

interface LegacyCustomerImporter
{
    /**
     * Import customers represented by legacy orders and return their ID map.
     *
     * @param  array<int, array<string, mixed>>  $orders
     * @return array<string, int>
     */
    public function import(array $orders): array;
}
