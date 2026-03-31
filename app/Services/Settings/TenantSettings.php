<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\Storage;

final readonly class TenantSettings
{
    private const string DEFAULT_HERO_IMAGE = 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

    private const string CATERING_HERO_IMAGE = 'https://images.unsplash.com/photo-1555244162-803834f70033?w=1920&q=80';

    /**
     * @param array<int, array<string, mixed>> $deliveryFeeTiers
     * @param array<int, string> $paymentMethodsAccepted
     * @param array<string, mixed> $operatingHours
     * @param array<int, array<string, mixed>> $faqItems
     * @param array<string, string> $socialMediaLinks
     * @param array<string, array<string, mixed>> $homepageSections
     */
    public function __construct(
        public string $storeName,
        public ?string $storeEmail,
        public ?string $storePhone,
        public ?string $storeAddress,
        public ?string $storeWebsite,
        public ?string $storeLogo,
        public ?string $storeTagline,
        public string $storefrontTheme,
        public ?string $businessTagline,
        public ?string $aboutUsText,
        public ?string $heroImage,
        public string $heroStyle,
        public ?string $allergyDisclaimer,
        public ?string $cateringHeroImage,
        public ?string $loyaltyHeroImage,
        public ?string $giftCardsHeroImage,
        public int $leadTimeHours,
        public bool $deliveryEnabled,
        public string $freeDeliveryMinimum,
        public array $deliveryFeeTiers,
        public array $paymentMethodsAccepted,
        public array $operatingHours,
        public array $faqItems,
        public string $loyaltyProgramName,
        public string $loyaltyPointsPerDollar,
        public bool $loyaltyEnabled,
        public string $cateringMinimumGuests,
        public string $cateringLeadTimeDays,
        public array $socialMediaLinks,
        public array $homepageSections,
    ) {}

    public static function resolve(): self
    {
        return new self(
            storeName: (string) settings('store_name', 'Our Bakery'),
            storeEmail: settings('store_email'),
            storePhone: settings('store_phone'),
            storeAddress: settings('store_address'),
            storeWebsite: settings('store_website'),
            storeLogo: settings('store_logo'),
            storeTagline: settings('store_tagline'),
            storefrontTheme: (string) settings('storefront_theme', 'classic'),
            businessTagline: settings('business_tagline'),
            aboutUsText: settings('about_us_text'),
            heroImage: settings('hero_image'),
            heroStyle: (string) settings('hero_style', 'split'),
            allergyDisclaimer: settings('allergy_disclaimer'),
            cateringHeroImage: settings('catering_hero_image'),
            loyaltyHeroImage: settings('loyalty_hero_image'),
            giftCardsHeroImage: settings('gift_cards_hero_image'),
            leadTimeHours: (int) settings('order_lead_time_hours', '24'),
            deliveryEnabled: settings('delivery_enabled', '1') === '1',
            freeDeliveryMinimum: (string) settings('free_delivery_minimum', '50'),
            deliveryFeeTiers: (array) json_decode((string) settings('delivery_fee_tiers', '[]'), true),
            paymentMethodsAccepted: (array) json_decode((string) settings('payment_methods_accepted', '[]'), true),
            operatingHours: (array) json_decode((string) settings('operating_hours', '{}'), true),
            faqItems: (array) json_decode((string) settings('faq_items', '[]'), true),
            loyaltyProgramName: (string) settings('loyalty_program_name', 'Rewards'),
            loyaltyPointsPerDollar: (string) settings('loyalty_points_per_dollar', '10'),
            loyaltyEnabled: settings('loyalty_enabled', '1') === '1',
            cateringMinimumGuests: (string) settings('catering_minimum_guests', '10'),
            cateringLeadTimeDays: (string) settings('catering_lead_time_days', '14'),
            socialMediaLinks: (array) json_decode((string) settings('social_media_links', '{}'), true),
            homepageSections: (array) json_decode((string) settings('homepage_sections', '{}'), true),
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

    public function storeLogoUrl(): ?string
    {
        return $this->storeLogo
            ? asset("storage/{$this->storeLogo}")
            : null;
    }

    public function defaultTagline(): string
    {
        return $this->storeTagline ?? "{$this->storeName} — Fresh baked goods made with love";
    }

    public function leadTimeDays(): int
    {
        return (int) ceil($this->leadTimeHours / 24);
    }

    /** @return \Illuminate\Support\Collection<string, array<string, mixed>> */
    public function visibleHomepageSections(): \Illuminate\Support\Collection
    {
        return collect($this->homepageSections)
            ->filter(fn (array $s) => $s['visible'] ?? true)
            ->sortBy('order');
    }
}
