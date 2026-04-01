<?php

namespace App\Enums\Marketing;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasColor, HasLabel
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';

    public function getLabel(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::TikTok => 'TikTok',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Instagram => 'pink',
            self::Facebook => 'info',
            self::TikTok => 'gray',
        };
    }

    public function maxChars(): int
    {
        return match ($this) {
            self::Instagram => 2200,
            self::Facebook => 63206,
            self::TikTok => 4000,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
