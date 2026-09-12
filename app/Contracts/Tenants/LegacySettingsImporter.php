<?php

namespace App\Contracts\Tenants;

interface LegacySettingsImporter
{
    /** @param array<int, array<string, mixed>> $settings */
    public function import(array $settings): void;
}
