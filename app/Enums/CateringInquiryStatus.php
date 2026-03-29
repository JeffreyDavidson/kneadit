<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CateringInquiryStatus: string implements HasLabel
{
    case Inquiry = 'inquiry';
    case Quoted = 'quoted';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Inquiry => 'New Inquiry',
            self::Quoted => 'Quote Sent',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
