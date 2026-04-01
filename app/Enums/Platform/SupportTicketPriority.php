<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum SupportTicketPriority: string implements HasLabel
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
