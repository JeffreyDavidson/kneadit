<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasLabel;

enum WaitlistStatus: string implements HasLabel
{
    case Waiting = 'waiting';
    case Notified = 'notified';
    case Converted = 'converted';
    case Removed = 'removed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
