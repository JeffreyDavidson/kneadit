<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPostStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Posted = 'posted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
