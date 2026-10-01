<?php

declare(strict_types=1);

namespace App\Enums\Inventory;

use Filament\Support\Contracts\HasLabel;

/**
 * What a MeasurementUnit measures. Units convert only within one dimension:
 * mass to mass, volume to volume, count to count.
 */
enum UnitDimension: string implements HasLabel
{
    case Mass = 'mass';
    case Volume = 'volume';
    case Count = 'count';

    public function getLabel(): string
    {
        return match ($this) {
            self::Mass => 'Mass',
            self::Volume => 'Volume',
            self::Count => 'Count',
        };
    }
}
