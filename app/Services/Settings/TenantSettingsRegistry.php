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

/**
 * Resolves and caches TenantSettings for the current tenant.
 *
 * Consumers never interact with this directly — they type-hint the DTO
 * they need (e.g., OrderSettings) or the full TenantSettings object,
 * and the container resolves it through bindings registered in AppServiceProvider.
 */
class TenantSettingsRegistry
{
    private ?TenantSettings $resolved = null;

    /**
     * Get the full settings object (lazy-resolved, cached for the request).
     */
    public function all(): TenantSettings
    {
        return $this->resolved ??= TenantSettings::resolve();
    }

    /**
     * Flush the cached instance.
     * Called by TenancyManager when switching tenants or after saving settings.
     */
    public function flush(): void
    {
        $this->resolved = null;
    }

    public function store(): StoreInfo
    {
        return $this->all()->store;
    }

    public function branding(): BrandingSettings
    {
        return $this->all()->branding;
    }

    public function orders(): OrderSettings
    {
        return $this->all()->orders;
    }

    public function payment(): PaymentSettings
    {
        return $this->all()->payment;
    }

    public function loyalty(): LoyaltySettings
    {
        return $this->all()->loyalty;
    }

    public function catering(): CateringSettings
    {
        return $this->all()->catering;
    }

    public function engagement(): EngagementSettings
    {
        return $this->all()->engagement;
    }

    public function policies(): PolicySettings
    {
        return $this->all()->policies;
    }

    public function webhooks(): WebhookSettings
    {
        return $this->all()->webhooks;
    }

    public function homepage(): HomepageSettings
    {
        return $this->all()->homepage;
    }

    public function onboarding(): OnboardingSettings
    {
        return $this->all()->onboarding;
    }
}
