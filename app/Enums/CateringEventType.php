<?php

namespace App\Enums;

enum CateringEventType: string
{
    case Wedding = 'wedding';
    case Corporate = 'corporate';
    case Birthday = 'birthday';
    case Holiday = 'holiday';
    case Other = 'other';

    public function label(): string
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
