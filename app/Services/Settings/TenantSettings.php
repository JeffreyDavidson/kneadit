<?php

namespace App\Services\Settings;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\CateringSettings;
use App\DataTransferObjects\Settings\EngagementSettings;
use App\DataTransferObjects\Settings\GiftCardSettings;
use App\DataTransferObjects\Settings\HomepageSettings;
use App\DataTransferObjects\Settings\InventorySettings;
use App\DataTransferObjects\Settings\LoyaltySettings;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\DataTransferObjects\Settings\OrderSettings;
use App\DataTransferObjects\Settings\PaymentSettings;
use App\DataTransferObjects\Settings\PolicySettings;
use App\DataTransferObjects\Settings\StoreInfo;
use App\DataTransferObjects\Settings\WebhookSettings;
use Illuminate\Support\Collection;

/**
 * Read-only composite DTO exposing tenant-scoped settings via typed sub-DTOs.
 * Each sub-DTO owns its own resolve() factory; TenantSettings::resolve() is
 * a thin composer.
 */
final readonly class TenantSettings
{
    public function __construct(
        public StoreInfo $store,
        public BrandingSettings $branding,
        public OrderSettings $orders,
        public PaymentSettings $payment,
        public CateringSettings $catering,
        public LoyaltySettings $loyalty,
        public EngagementSettings $engagement,
        public PolicySettings $policies,
        public HomepageSettings $homepage,
        public OnboardingSettings $onboarding,
        public WebhookSettings $webhooks,
        public GiftCardSettings $giftCards,
        public InventorySettings $inventory,
    ) {}

    public static function resolve(): self
    {
        return new self(
            store: StoreInfo::resolve(),
            branding: BrandingSettings::resolve(),
            orders: OrderSettings::resolve(),
            payment: PaymentSettings::resolve(),
            catering: CateringSettings::resolve(),
            loyalty: LoyaltySettings::resolve(),
            engagement: EngagementSettings::resolve(),
            policies: PolicySettings::resolve(),
            homepage: HomepageSettings::resolve(),
            onboarding: OnboardingSettings::resolve(),
            webhooks: WebhookSettings::resolve(),
            giftCards: GiftCardSettings::resolve(),
            inventory: InventorySettings::resolve(),
        );
    }

    // ──────────────────────────────────────────────────────────
    // Convenience delegates
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
        return $this->store->logoUrl();
    }

    public function defaultTagline(): string
    {
        return $this->store->defaultTagline();
    }

    public function leadTimeDays(): int
    {
        return $this->orders->leadTimeDays();
    }

    /** @return Collection<string, array<string, mixed>> */
    public function visibleHomepageSections(): Collection
    {
        return $this->homepage->visibleSections();
    }
}
