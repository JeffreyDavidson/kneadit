<?php

namespace App\DataTransferObjects\Settings;

use Illuminate\Support\Facades\Storage;

final readonly class BrandingSettings
{
    public const string DEFAULT_BRAND_COLOR = '#d4920c';

    private const string DEFAULT_HERO_IMAGE = 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

    private const string CATERING_HERO_IMAGE = 'https://images.unsplash.com/photo-1555244162-803834f70033?w=1920&q=80';

    public function __construct(
        public string $brandColorPrimary,
        public string $storefrontTheme,
        public ?string $businessTagline,
        public ?string $aboutUsText,
        public ?string $heroImage,
        public string $heroStyle,
        public ?string $heroTagline,
        public string $heroPrimaryCtaText,
        public string $heroSecondaryCtaText,
        public ?string $allergyDisclaimer,
        public ?string $cateringHeroImage,
        public ?string $loyaltyHeroImage,
        public ?string $giftCardsHeroImage,
    ) {}

    public static function resolve(): self
    {
        return new self(
            brandColorPrimary: SettingValue::string(tenant('brand_color_primary'), self::DEFAULT_BRAND_COLOR),
            storefrontTheme: SettingValue::string(settings('storefront_theme'), 'classic'),
            businessTagline: SettingValue::nullableString(settings('business_tagline')),
            aboutUsText: SettingValue::nullableString(settings('about_us_text')),
            heroImage: SettingValue::nullableString(settings('hero_image')),
            heroStyle: SettingValue::string(settings('hero_style'), 'split'),
            heroTagline: SettingValue::nullableString(settings('hero_tagline')),
            heroPrimaryCtaText: SettingValue::string(settings('hero_primary_cta_text'), 'Order Now'),
            heroSecondaryCtaText: SettingValue::string(settings('hero_secondary_cta_text'), 'Browse Menu'),
            allergyDisclaimer: SettingValue::nullableString(settings('allergy_disclaimer')),
            cateringHeroImage: SettingValue::nullableString(settings('catering_hero_image')),
            loyaltyHeroImage: SettingValue::nullableString(settings('loyalty_hero_image')),
            giftCardsHeroImage: SettingValue::nullableString(settings('gift_cards_hero_image')),
        );
    }

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
