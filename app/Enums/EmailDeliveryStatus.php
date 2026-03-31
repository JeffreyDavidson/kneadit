<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmailDeliveryStatus: string implements HasLabel
{
    case Sent = 'sent';
    case Failed = 'failed';
    case Opened = 'opened';
    case Bounced = 'bounced';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
