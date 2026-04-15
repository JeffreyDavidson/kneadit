<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CateringInquiryStatus: string implements HasColor, HasIcon, HasLabel
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

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Inquiry => Heroicon::OutlinedSparkles,
            self::Quoted => Heroicon::OutlinedDocumentText,
            self::Confirmed => Heroicon::OutlinedCheckCircle,
            self::Completed => Heroicon::OutlinedCheckBadge,
            self::Cancelled => Heroicon::OutlinedXCircle,
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
