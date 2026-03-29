<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CateringEventType: string implements HasLabel
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
}
