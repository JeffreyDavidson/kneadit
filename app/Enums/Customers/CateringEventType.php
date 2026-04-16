<?php

namespace App\Enums\Customers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CateringEventType: string implements HasColor, HasLabel
{
    case Wedding = 'wedding';
    case Corporate = 'corporate';
    case Birthday = 'birthday';
    case Holiday = 'holiday';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Wedding => '💒 Wedding',
            self::Corporate => '🏢 Corporate',
            self::Birthday => '🎂 Birthday',
            self::Holiday => '🎄 Holiday',
            self::Other => '🎉 Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Wedding => 'danger',
            self::Corporate => 'info',
            self::Birthday => 'warning',
            self::Holiday => 'success',
            self::Other => 'gray',
        };
    }
}
