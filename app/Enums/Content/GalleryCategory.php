<?php

namespace App\Enums\Content;

use Filament\Support\Contracts\HasLabel;

enum GalleryCategory: string implements HasLabel
{
    case Products = 'products';
    case Bakery = 'bakery';
    case Team = 'team';
    case Events = 'events';
    case Process = 'process';
    case Other = 'other';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
