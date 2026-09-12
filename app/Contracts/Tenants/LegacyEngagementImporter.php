<?php

namespace App\Contracts\Tenants;

interface LegacyEngagementImporter
{
    /**
     * @param array<int, array<string, mixed>> $contactMessages
     * @param array<int, array<string, mixed>> $waitlistEntries
     * @param array<int, array<string, mixed>> $favorites
     * @param array<int, int> $productIds
     */
    public function import(array $contactMessages, array $waitlistEntries, array $favorites, array $productIds): void;
}
