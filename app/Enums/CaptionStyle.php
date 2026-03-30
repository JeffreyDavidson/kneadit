<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CaptionStyle: string implements HasLabel
{
    case Playful = 'playful';
    case Professional = 'professional';
    case Seasonal = 'seasonal';
    case Storytelling = 'storytelling';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
