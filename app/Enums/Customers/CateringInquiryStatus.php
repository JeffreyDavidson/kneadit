<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CateringInquiryStatus: string implements HasColor, HasLabel
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

    public function getColor(): string
    {
        return match ($this) {
            self::Inquiry => 'gray',
            self::Quoted => 'info',
            self::Confirmed => 'success',
            self::Completed => 'primary',
            self::Cancelled => 'danger',
        };
    }
}
