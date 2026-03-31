<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailCampaignSegment: string implements HasLabel
{
    case All = 'all';
    case Starter = 'starter';
    case Growth = 'growth';
    case Pro = 'pro';
    case Trial = 'trial';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
