<?php

namespace App\Enums\Financial;

enum MarginHealth: string
{
    case Healthy = 'green';
    case Warning = 'yellow';
    case Critical = 'red';
    case Unknown = 'gray';

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
