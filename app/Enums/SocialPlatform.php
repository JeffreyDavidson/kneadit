<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
