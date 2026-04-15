<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MarginHealth: string implements HasColor, HasLabel
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

    public function getColor(): string
    {
        return match ($this) {
            self::Healthy => 'success',
            self::Warning => 'warning',
            self::Critical => 'danger',
            self::Unknown => 'gray',
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
