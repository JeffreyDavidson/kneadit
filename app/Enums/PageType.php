<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageType: string implements HasLabel
{
    case Menu = 'menu';
    case Home = 'home';
    case About = 'about';
    case Reviews = 'reviews';
    case Order = 'order';
    case Track = 'track';
    case Contact = 'contact';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
