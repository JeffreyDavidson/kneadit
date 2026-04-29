<?php

namespace App\Enums\Operations;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ActivityAction: string implements HasColor, HasLabel
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';

    public function getLabel(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Created => 'success',
            self::Updated => 'info',
            self::Deleted => 'danger',
        };
    }

    /**
     * Tailwind background class for the action pill on the
     * Recent Activity dashboard widget. Mirrors the central
     * panel's audit log color pattern.
     */
    public function pillClass(): string
    {
        return match ($this) {
            self::Created => 'bg-green-500',
            self::Updated => 'bg-blue-500',
            self::Deleted => 'bg-red-500',
        };
    }
}
