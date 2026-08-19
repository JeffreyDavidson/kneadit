<?php

namespace App\Enums\Tenants;

use Filament\Support\Contracts\HasLabel;

enum ChurnSeverity: string implements HasLabel
{
    case Warning = 'warning';
    case Critical = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::Warning => 'Warning',
            self::Critical => 'Critical',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::Warning => 0,
            self::Critical => 1,
        };
    }
}
