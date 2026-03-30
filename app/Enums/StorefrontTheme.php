<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StorefrontTheme: string implements HasLabel
{
    case Classic = 'classic';
    case Modern = 'modern';
    case Rustic = 'rustic';
    case Elegant = 'elegant';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
