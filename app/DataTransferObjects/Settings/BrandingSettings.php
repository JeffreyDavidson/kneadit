<?php

namespace App\DataTransferObjects\Settings;

use Illuminate\Support\Facades\Storage;

final readonly class BrandingSettings
{
    private const string DEFAULT_HERO_IMAGE = 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

    private const string CATERING_HERO_IMAGE = 'https://images.unsplash.com/photo-1555244162-803834f70033?w=1920&q=80';

    public function __construct(
        public string $brandColorPrimary,
        public string $storefrontTheme,
        public ?string $businessTagline,
        public ?string $aboutUsText,
        public ?string $heroImage,
        public string $heroStyle,
        public ?string $allergyDisclaimer,
        public ?string $cateringHeroImage,
        public ?string $loyaltyHeroImage,
        public ?string $giftCardsHeroImage,
    ) {}

    public function heroImageUrl(): string
    {
        return $this->heroImage
            ? Storage::url($this->heroImage)
            : self::DEFAULT_HERO_IMAGE;
    }

    public function cateringHeroImageUrl(): string
    {
        return $this->cateringHeroImage
            ? Storage::url($this->cateringHeroImage)
            : self::CATERING_HERO_IMAGE;
    }

    public function loyaltyHeroImageUrl(): string
    {
        return $this->loyaltyHeroImage
            ? Storage::url($this->loyaltyHeroImage)
            : self::DEFAULT_HERO_IMAGE;
    }

    public function giftCardsHeroImageUrl(): string
    {
        return $this->giftCardsHeroImage
            ? Storage::url($this->giftCardsHeroImage)
            : self::DEFAULT_HERO_IMAGE;
    }
}
