<?php

namespace App\Enums;

enum EmailCampaignStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
