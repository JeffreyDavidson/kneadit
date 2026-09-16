<?php

namespace App\Services\Tenants\Contracts;

interface LegacyCouponImporter
{
    /**
     * Import legacy coupons and return their legacy-to-current ID map.
     *
     * @param  array<int, array<string, mixed>>  $coupons
     * @return array<int, int>
     */
    public function import(array $coupons): array;
}
