<?php

namespace App\Enums;

enum SocialPostStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Posted = 'posted';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
