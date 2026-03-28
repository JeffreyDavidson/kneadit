<?php

namespace App\Enums;

enum WaitlistStatus: string
{
    case Waiting = 'waiting';
    case Notified = 'notified';
    case Converted = 'converted';
    case Removed = 'removed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
