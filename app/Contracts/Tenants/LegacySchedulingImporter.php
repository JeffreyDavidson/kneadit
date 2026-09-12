<?php

namespace App\Contracts\Tenants;

interface LegacySchedulingImporter
{
    /**
     * @param array<int, array<string, mixed>> $capacityLimits
     * @param array<int, array<string, mixed>> $holidays
     */
    public function import(array $capacityLimits, array $holidays): void;
}
