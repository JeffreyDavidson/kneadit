<?php

declare(strict_types=1);

namespace App\Services\Tenants\Contracts;

interface LegacySettingsImporter
{
    /** @param array<int, array<string, mixed>> $settings */
    public function import(array $settings): void;
}
