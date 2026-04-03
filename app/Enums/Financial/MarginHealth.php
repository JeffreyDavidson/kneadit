<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasLabel;

enum MarginHealth: string implements HasLabel
{
    case Healthy = 'green';
    case Warning = 'yellow';
    case Critical = 'red';
    case Unknown = 'gray';

    public function getLabel(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Warning => 'Warning',
            self::Critical => 'Critical',
            self::Unknown => 'Unknown',
        };
    }

    public static function fromPercentage(?float $margin): self
    {
        if ($margin === null) {
            return self::Unknown;
        }

        return match (true) {
            $margin >= 50 => self::Healthy,
            $margin >= 30 => self::Warning,
            default => self::Critical,
        };
    }

    public function cssClass(): string
    {
        return $this->value;
    }
}
