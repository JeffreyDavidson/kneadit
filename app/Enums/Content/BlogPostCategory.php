<?php

namespace App\Enums\Content;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

enum BlogPostCategory: string implements HasColor, HasLabel
{
    case Guides = 'guides';
    case Laws = 'laws';
    case Tips = 'tips';
    case News = 'news';

    public function getLabel(): string
    {
        return match ($this) {
            self::Guides => 'Getting Started',
            self::Laws => 'Cottage Food Laws',
            self::Tips => 'Baker Tips',
            self::News => 'KneadIt News',
        };
    }

    /** @return Collection<string, string> */
    public static function options(): Collection
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->prepend('All Posts', 'all');
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Guides => 'primary',
            self::Tips => 'success',
            self::Laws => 'warning',
            self::News => 'info',
        };
    }
}
