<?php

declare(strict_types=1);

namespace App\Enums\Storefront;

use Filament\Support\Contracts\HasLabel;

/**
 * The storefront hero image settings. Each case value is the setting key that
 * BrandingSettings reads the stored path from.
 */
enum StorefrontHeroImage: string implements HasLabel
{
    case Homepage = 'hero_image';
    case Catering = 'catering_hero_image';
    case Loyalty = 'loyalty_hero_image';
    case GiftCards = 'gift_cards_hero_image';

    public function getLabel(): string
    {
        return match ($this) {
            self::Homepage => 'Homepage hero',
            self::Catering => 'Catering page hero',
            self::Loyalty => 'Rewards page hero',
            self::GiftCards => 'Gift cards page hero',
        };
    }
}
