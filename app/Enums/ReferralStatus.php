<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReferralStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Rewarded = 'rewarded';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
