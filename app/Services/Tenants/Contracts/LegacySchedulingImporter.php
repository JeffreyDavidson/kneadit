<?php

namespace App\Services\Tenants\Contracts;

interface LegacySchedulingImporter
{
    /**
     * @param array<int, array<string, mixed>> $capacityLimits
     * @param array<int, array<string, mixed>> $holidays
     */
    public function import(array $capacityLimits, array $holidays): void;
}
