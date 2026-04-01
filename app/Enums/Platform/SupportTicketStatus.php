<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum SupportTicketStatus: string implements HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::InProgress => 'In Progress',
            default => ucfirst($this->value),
        };
    }
}
