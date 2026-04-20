<?php

namespace App\Services\Settings;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\CateringSettings;
use App\DataTransferObjects\Settings\EngagementSettings;
use App\DataTransferObjects\Settings\HomepageSettings;
use App\DataTransferObjects\Settings\LoyaltySettings;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\DataTransferObjects\Settings\OrderSettings;
use App\DataTransferObjects\Settings\PaymentSettings;
use App\DataTransferObjects\Settings\PolicySettings;
use App\DataTransferObjects\Settings\StoreInfo;
use App\DataTransferObjects\Settings\WebhookSettings;
use App\Enums\Customers\CateringEventType;

final class TenantSettings
{
    public const string DEFAULT_BRAND_COLOR = '#d4920c';

    /**
     * @param array<int, array<string, mixed>> $deliveryFeeTiers
     * @param array<int, string> $paymentMethodsAccepted
     * @param array<string, mixed> $operatingHours
     * @param array<int, array<string, mixed>> $faqItems
     * @param array<string, string> $socialMediaLinks
     * @param array<string, array<string, mixed>> $homepageSections
     * @param array<int, string> $cateringEventTypes
     */
    public function __construct(
        public readonly string $storeName,
        public readonly ?string $storeEmail,
        public readonly ?string $storePhone,
        public readonly ?string $storeAddress,
        public readonly ?string $storeWebsite,
        public readonly ?string $storeLogo,
        public readonly ?string $storeTagline,
        public readonly string $brandColorPrimary,
        public readonly ?string $onboardingCompletedAt,
        public readonly string $storefrontTheme,
        public readonly ?string $businessTagline,
        public readonly ?string $aboutUsText,
        public readonly ?string $heroImage,
        public readonly string $heroStyle,
        public readonly ?string $heroTagline,
        public readonly string $heroPrimaryCtaText,
        public readonly string $heroSecondaryCtaText,
        public readonly ?string $allergyDisclaimer,
        public readonly ?string $cateringHeroImage,
        public readonly ?string $loyaltyHeroImage,
        public readonly ?string $giftCardsHeroImage,
        public readonly int $leadTimeHours,
        public readonly bool $deliveryEnabled,
        public readonly string $freeDeliveryMinimum,
        public readonly string $minimumPickupOrderAmount,
        public readonly string $minimumDeliveryOrderAmount,
        public readonly array $deliveryFeeTiers,
        public readonly int $defaultDailyCapacity,
        public readonly array $paymentMethodsAccepted,
        public readonly array $operatingHours,
        public readonly array $faqItems,
        public readonly string $loyaltyProgramName,
        public readonly int $loyaltyPointsPerDollar,
        public readonly bool $loyaltyEnabled,
        public readonly string $cateringMinimumGuests,
        public readonly string $cateringLeadTimeDays,
        public readonly array $cateringEventTypes,
        public readonly array $socialMediaLinks,
        public readonly array $homepageSections,
        public readonly bool $cateringEnabled,
        public readonly ?string $storePhoto,
        public readonly bool $announcementEnabled,
        public readonly string $announcementText,
        public readonly string $announcementType,
        public readonly bool $showPolicies,
        public readonly string $cancellationPolicy,
        public readonly string $depositPolicy,
        public readonly string $refundPolicy,
        public readonly string $pickupPolicy,
        public readonly string $additionalTerms,
        public readonly bool $birthdayProgramEnabled,
        public readonly bool $birthdayCouponEnabled,
        public readonly int $birthdayDiscountPercentage,
        public readonly int $birthdayCouponValidDays,
        public readonly bool $reviewRequestsEnabled,
        public readonly int $reviewRequestDelayHours,
        public readonly bool $repeatRemindersEnabled,
        public readonly int $repeatReminderDays,
        /** @var array<int, int> */
        public readonly array $giftCardPresetAmounts,
        public readonly int $giftCardDefaultAmount,
    ) {}

    // ──────────────────────────────────────────────────────────
    // Sub-DTO virtual properties
    // ──────────────────────────────────────────────────────────

    public StoreInfo $store {
        get => new StoreInfo(
            name: $this->storeName,
            email: $this->storeEmail,
            phone: $this->storePhone,
            address: $this->storeAddress,
            website: $this->storeWebsite,
            photo: $this->storePhoto,
            logo: $this->storeLogo,
            tagline: $this->storeTagline,
        );
    }

    public LoyaltySettings $loyalty {
        get => new LoyaltySettings(
            enabled: $this->loyaltyEnabled,
            pointsPerDollar: $this->loyaltyPointsPerDollar,
            programName: $this->loyaltyProgramName,
        );
    }

    public OrderSettings $orders {
        get => new OrderSettings(
            leadTimeHours: $this->leadTimeHours,
            deliveryEnabled: $this->deliveryEnabled,
            freeDeliveryMinimum: $this->freeDeliveryMinimum,
            minimumPickupOrderAmount: $this->minimumPickupOrderAmount,
            minimumDeliveryOrderAmount: $this->minimumDeliveryOrderAmount,
            deliveryFeeTiers: $this->deliveryFeeTiers,
            defaultDailyCapacity: $this->defaultDailyCapacity,
        );
    }

    public BrandingSettings $branding {
        get => new BrandingSettings(
            brandColorPrimary: $this->brandColorPrimary,
            storefrontTheme: $this->storefrontTheme,
            businessTagline: $this->businessTagline,
            aboutUsText: $this->aboutUsText,
            heroImage: $this->heroImage,
            heroStyle: $this->heroStyle,
            heroTagline: $this->heroTagline,
            heroPrimaryCtaText: $this->heroPrimaryCtaText,
            heroSecondaryCtaText: $this->heroSecondaryCtaText,
            allergyDisclaimer: $this->allergyDisclaimer,
            cateringHeroImage: $this->cateringHeroImage,
            loyaltyHeroImage: $this->loyaltyHeroImage,
            giftCardsHeroImage: $this->giftCardsHeroImage,
        );
    }

    public PaymentSettings $payment {
        get => new PaymentSettings(
            methodsAccepted: $this->paymentMethodsAccepted,
        );
    }

    public CateringSettings $catering {
        get => new CateringSettings(
            enabled: $this->cateringEnabled,
            minimumGuests: $this->cateringMinimumGuests,
            leadTimeDays: $this->cateringLeadTimeDays,
            eventTypes: $this->cateringEventTypes,
        );
    }

    public EngagementSettings $engagement {
        get => new EngagementSettings(
            birthdayProgramEnabled: $this->birthdayProgramEnabled,
            birthdayCouponEnabled: $this->birthdayCouponEnabled,
            birthdayDiscountPercentage: $this->birthdayDiscountPercentage,
            birthdayCouponValidDays: $this->birthdayCouponValidDays,
            reviewRequestsEnabled: $this->reviewRequestsEnabled,
            reviewRequestDelayHours: $this->reviewRequestDelayHours,
            repeatRemindersEnabled: $this->repeatRemindersEnabled,
            repeatReminderDays: $this->repeatReminderDays,
            announcementEnabled: $this->announcementEnabled,
            announcementText: $this->announcementText,
            announcementType: $this->announcementType,
        );
    }

    public PolicySettings $policies {
        get => new PolicySettings(
            showOnStorefront: $this->showPolicies,
            cancellation: $this->cancellationPolicy,
            deposit: $this->depositPolicy,
            refund: $this->refundPolicy,
            pickup: $this->pickupPolicy,
            additionalTerms: $this->additionalTerms,
        );
    }

    public HomepageSettings $homepage {
        get => new HomepageSettings(
            socialMediaLinks: $this->socialMediaLinks,
            operatingHours: $this->operatingHours,
            faqItems: $this->faqItems,
            sections: $this->homepageSections,
        );
    }

    public WebhookSettings $webhooks {
        get => new WebhookSettings;
    }

    public OnboardingSettings $onboarding {
        get => new OnboardingSettings(
            completedAt: $this->onboardingCompletedAt,
        );
    }

    // ──────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────

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
            brandColorPrimary: (string) (tenant()->brand_color_primary ?? self::DEFAULT_BRAND_COLOR),
            onboardingCompletedAt: settings('onboarding_completed_at'),
            storefrontTheme: (string) settings('storefront_theme', 'classic'),
            businessTagline: settings('business_tagline'),
            aboutUsText: settings('about_us_text'),
            heroImage: settings('hero_image'),
            heroStyle: (string) settings('hero_style', 'split'),
            heroTagline: settings('hero_tagline'),
            heroPrimaryCtaText: (string) settings('hero_primary_cta_text', 'Order Now'),
            heroSecondaryCtaText: (string) settings('hero_secondary_cta_text', 'Browse Menu'),
            allergyDisclaimer: settings('allergy_disclaimer'),
            cateringHeroImage: settings('catering_hero_image'),
            loyaltyHeroImage: settings('loyalty_hero_image'),
            giftCardsHeroImage: settings('gift_cards_hero_image'),
            leadTimeHours: (int) settings('order_lead_time_hours', '24'),
            deliveryEnabled: settings('delivery_enabled', '1') === '1',
            freeDeliveryMinimum: (string) settings('free_delivery_minimum', '50'),
            minimumPickupOrderAmount: (string) settings('minimum_pickup_order_amount', '0'),
            minimumDeliveryOrderAmount: (string) settings('minimum_delivery_order_amount', '0'),
            deliveryFeeTiers: (array) json_decode((string) settings('delivery_fee_tiers', '[]'), true),
            defaultDailyCapacity: (int) settings('default_daily_capacity', '20'),
            paymentMethodsAccepted: (array) json_decode((string) settings('payment_methods_accepted', '[]'), true),
            operatingHours: (array) json_decode((string) settings('operating_hours', '{}'), true),
            faqItems: (array) json_decode((string) settings('faq_items', '[]'), true),
            loyaltyProgramName: (string) settings('loyalty_program_name', 'Rewards'),
            loyaltyPointsPerDollar: (int) settings('loyalty_points_per_dollar', '10'),
            loyaltyEnabled: settings('loyalty_enabled', '1') === '1',
            cateringMinimumGuests: (string) settings('catering_minimum_guests', '10'),
            cateringLeadTimeDays: (string) settings('catering_lead_time_days', '14'),
            cateringEventTypes: self::resolveCateringEventTypes(),
            socialMediaLinks: (array) json_decode((string) settings('social_media_links', '{}'), true),
            homepageSections: (array) json_decode((string) settings('homepage_sections', '{}'), true),
            cateringEnabled: settings('catering_enabled', '0') === '1',
            storePhoto: settings('store_photo'),
            announcementEnabled: settings('announcement_enabled', '0') === '1',
            announcementText: (string) settings('announcement_text', ''),
            announcementType: (string) settings('announcement_type', 'info'),
            showPolicies: settings('show_policies_on_storefront', '0') === '1',
            cancellationPolicy: (string) settings('cancellation_policy', ''),
            depositPolicy: (string) settings('deposit_policy', ''),
            refundPolicy: (string) settings('refund_policy', ''),
            pickupPolicy: (string) settings('pickup_policy', ''),
            additionalTerms: (string) settings('additional_terms', ''),
            birthdayProgramEnabled: settings('birthday_program_enabled', '0') === '1',
            birthdayCouponEnabled: settings('birthday_coupon_enabled', '1') === '1',
            birthdayDiscountPercentage: (int) settings('birthday_discount_percentage', '15'),
            birthdayCouponValidDays: (int) settings('birthday_coupon_valid_days', '7'),
            reviewRequestsEnabled: settings('review_requests_enabled', '0') === '1',
            reviewRequestDelayHours: (int) settings('review_request_delay_hours', '24'),
            repeatRemindersEnabled: settings('repeat_reminders_enabled', '0') === '1',
            repeatReminderDays: (int) settings('repeat_reminder_days', '30'),
            giftCardPresetAmounts: array_map('intval', array_filter(explode(',', (string) settings('gift_card_preset_amounts', '10,25,50,100')))),
            giftCardDefaultAmount: (int) settings('gift_card_default_amount', '25'),
        );
    }

    // ──────────────────────────────────────────────────────────
    // Convenience methods
    // ──────────────────────────────────────────────────────────

    public function heroImageUrl(): string
    {
        return $this->branding->heroImageUrl();
    }

    public function cateringHeroImageUrl(): string
    {
        return $this->branding->cateringHeroImageUrl();
    }

    public function loyaltyHeroImageUrl(): string
    {
        return $this->branding->loyaltyHeroImageUrl();
    }

    public function giftCardsHeroImageUrl(): string
    {
        return $this->branding->giftCardsHeroImageUrl();
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
        return $this->orders->leadTimeDays();
    }

    /** @return array<int, string> */
    private static function resolveCateringEventTypes(): array
    {
        $stored = settings('catering_event_types');

        if (! $stored) {
            return CateringEventType::defaultLabels();
        }

        $decoded = json_decode((string) $stored, true);
        $values = array_values(array_filter(
            is_array($decoded) ? $decoded : [],
            fn ($value) => is_string($value) && trim($value) !== '',
        ));

        return $values === [] ? CateringEventType::defaultLabels() : $values;
    }

    /** @return \Illuminate\Support\Collection<string, array<string, mixed>> */
    public function visibleHomepageSections(): \Illuminate\Support\Collection
    {
        return $this->homepage->visibleSections();
    }
}
