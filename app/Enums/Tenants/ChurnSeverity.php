<?php

namespace App\Enums\Tenants;

enum ChurnSeverity: string
{
    case Warning = 'warning';
    case Critical = 'critical';

    public function priority(): int
    {
        return match ($this) {
            self::Warning => 0,
            self::Critical => 1,
        };
    }
}
