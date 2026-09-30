<?php

declare(strict_types=1);

namespace App\Enums\Storefront;

use Filament\Support\Contracts\HasLabel;

enum HeroStyle: string implements HasLabel
{
    case Split = 'split';
    case FullPhoto = 'fullphoto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Split => 'Split layout (text beside photo)',
            self::FullPhoto => 'Full photo background',
        };
    }
}
