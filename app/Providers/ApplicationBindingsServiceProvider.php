<?php

namespace App\Providers;

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
use App\Services\PayPal\Contracts\PayPalClient;
use App\Services\PayPal\HttpPayPalClient;
use App\Services\Platform\Contracts\ForgeClient;
use App\Services\Platform\HttpForgeClient;
use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use App\Services\Tenants\Contracts\LegacyCatalogImporter;
use App\Services\Tenants\Contracts\LegacyCouponImporter;
use App\Services\Tenants\Contracts\LegacyCustomerImporter;
use App\Services\Tenants\Contracts\LegacyEngagementImporter;
use App\Services\Tenants\Contracts\LegacyFinancialImporter;
use App\Services\Tenants\Contracts\LegacyOrderImporter;
use App\Services\Tenants\Contracts\LegacyOrderItemImporter;
use App\Services\Tenants\Contracts\LegacyRecipeImporter;
use App\Services\Tenants\Contracts\LegacyReviewImporter;
use App\Services\Tenants\Contracts\LegacySchedulingImporter;
use App\Services\Tenants\Contracts\LegacySettingsImporter;
use App\Services\Tenants\DatabaseLegacyCatalogImporter;
use App\Services\Tenants\DatabaseLegacyCouponImporter;
use App\Services\Tenants\DatabaseLegacyCustomerImporter;
use App\Services\Tenants\DatabaseLegacyEngagementImporter;
use App\Services\Tenants\DatabaseLegacyFinancialImporter;
use App\Services\Tenants\DatabaseLegacyOrderImporter;
use App\Services\Tenants\DatabaseLegacyOrderItemImporter;
use App\Services\Tenants\DatabaseLegacyRecipeImporter;
use App\Services\Tenants\DatabaseLegacyReviewImporter;
use App\Services\Tenants\DatabaseLegacySchedulingImporter;
use App\Services\Tenants\DatabaseLegacySettingsImporter;
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
        $this->app->bind(PayPalClient::class, HttpPayPalClient::class);
        $this->app->bind(ForgeClient::class, HttpForgeClient::class);
        $this->app->bind(LegacyCatalogImporter::class, DatabaseLegacyCatalogImporter::class);
        $this->app->bind(LegacyCouponImporter::class, DatabaseLegacyCouponImporter::class);
        $this->app->bind(LegacyCustomerImporter::class, DatabaseLegacyCustomerImporter::class);
        $this->app->bind(LegacyFinancialImporter::class, DatabaseLegacyFinancialImporter::class);
        $this->app->bind(LegacyEngagementImporter::class, DatabaseLegacyEngagementImporter::class);
        $this->app->bind(LegacyOrderItemImporter::class, DatabaseLegacyOrderItemImporter::class);
        $this->app->bind(LegacyOrderImporter::class, DatabaseLegacyOrderImporter::class);
        $this->app->bind(LegacyReviewImporter::class, DatabaseLegacyReviewImporter::class);
        $this->app->bind(LegacyRecipeImporter::class, DatabaseLegacyRecipeImporter::class);
        $this->app->bind(LegacySchedulingImporter::class, DatabaseLegacySchedulingImporter::class);
        $this->app->bind(LegacySettingsImporter::class, DatabaseLegacySettingsImporter::class);
        $this->app->scoped(SettingsManager::class);
        $this->app->scoped(PlatformSettingsManager::class);
        $this->app->scoped(TenantSettingsRegistry::class);
        $this->app->scoped(TenantSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->all());

        foreach (self::TENANT_SETTING_DTOS as $dto => $method) {
            $this->app->bind($dto, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->{$method}());
        }
    }
}
