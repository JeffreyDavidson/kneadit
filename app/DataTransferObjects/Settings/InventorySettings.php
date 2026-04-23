<?php

namespace App\DataTransferObjects\Settings;

final readonly class InventorySettings
{
    public function __construct(
        public bool $lowStockAlertsEnabled,
    ) {}

    public static function resolve(): self
    {
        return new self(
            lowStockAlertsEnabled: settings('low_stock_alerts_enabled', '0') === '1',
        );
    }
}
