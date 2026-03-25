<?php

namespace App\Enums;

enum CateringInquiryStatus: string
{
    case Inquiry = 'inquiry';
    case Quoted = 'quoted';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
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
