<?php

namespace App\Enums\Marketing;

use Filament\Support\Contracts\HasLabel;

enum EmailCampaignStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
