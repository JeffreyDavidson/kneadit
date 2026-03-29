<?php

namespace App\Enums;

enum BlogPostCategory: string
{
    case Guides = 'guides';
    case Laws = 'laws';
    case Tips = 'tips';
    case News = 'news';

    public function label(): string
    {
        return match ($this) {
            self::Guides => 'Getting Started',
            self::Laws => 'Cottage Food Laws',
            self::Tips => 'Baker Tips',
            self::News => 'KneadIt News',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Guides => 'primary',
            self::Tips => 'success',
            self::Laws => 'warning',
            self::News => 'info',
        };
    }
}
