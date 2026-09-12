<?php

namespace App\Providers;

use App\Contracts\Tenants\LegacyCatalogImporter;
use App\Contracts\Tenants\LegacyCouponImporter;
use App\Contracts\Tenants\LegacyCustomerImporter;
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
use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use App\Services\Tenants\DatabaseLegacyCatalogImporter;
use App\Services\Tenants\DatabaseLegacyCouponImporter;
use App\Services\Tenants\DatabaseLegacyCustomerImporter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ApplicationBindingsServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, string>
     */
    private const array TENANT_SETTING_DTOS = [
        StoreInfo::class => 'store',
        BrandingSettings::class => 'branding',
        OrderSettings::class => 'orders',
        PaymentSettings::class => 'payment',
        LoyaltySettings::class => 'loyalty',
        CateringSettings::class => 'catering',
        EngagementSettings::class => 'engagement',
        PolicySettings::class => 'policies',
        WebhookSettings::class => 'webhooks',
        HomepageSettings::class => 'homepage',
        OnboardingSettings::class => 'onboarding',
    ];

    public function register(): void
    {
        $this->app->bind(LegacyCatalogImporter::class, DatabaseLegacyCatalogImporter::class);
        $this->app->bind(LegacyCouponImporter::class, DatabaseLegacyCouponImporter::class);
        $this->app->bind(LegacyCustomerImporter::class, DatabaseLegacyCustomerImporter::class);
        $this->app->singleton(SettingsManager::class);
        $this->app->singleton(PlatformSettingsManager::class);
        $this->app->scoped(TenantSettingsRegistry::class);
        $this->app->scoped(TenantSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->all());

        foreach (self::TENANT_SETTING_DTOS as $dto => $method) {
            $this->app->bind($dto, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->{$method}());
        }
    }
}
