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
use App\Enums\Platform\SubscriptionTier;
use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use App\Support\Csp\CspNonce;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use RuntimeException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, string>
     */
    private const TENANT_SETTING_DTOS = [
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
        $this->app->singleton(SettingsManager::class);
        $this->app->singleton(PlatformSettingsManager::class);
        $this->app->singleton(TenantSettingsRegistry::class);
        $this->app->singleton(TenantSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->all());

        // Per-request scoped: SecurityHeaders middleware writes the nonce into
        // the CSP header, the @cspnonce Blade directive emits it on inline
        // <script>/<style> tags. Same value flows through the request.
        $this->app->scoped(CspNonce::class);

        foreach (self::TENANT_SETTING_DTOS as $dto => $method) {
            $this->app->bind($dto, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->{$method}());
        }
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        // Surface cache reads that returned __PHP_Incomplete_Class — usually
        // an Eloquent model/collection that was cached and then blocked by
        // cache.serializable_classes on read (see CLAUDE.md). Always log;
        // additionally throw in non-production so developers see the bad
        // write loudly during the read that exposes it.
        CacheRepository::handleUnserializableClassUsing(function (string $key, ?string $class): void {
            $message = sprintf(
                'Cache returned __PHP_Incomplete_Class for key [%s] (original class: %s). Likely a model/collection cached against the project rule (CLAUDE.md: never cache Eloquent models).',
                $key,
                $class ?? 'unknown',
            );

            Log::error($message);

            if (! app()->isProduction()) {
                throw new RuntimeException($message);
            }
        });

        RateLimiter::for('webhooks', fn () => Limit::perMinute(30));

        Feature::define('growth-features', fn (): bool => tenant()?->plan?->meetsRequirement(SubscriptionTier::Growth) ?? false);
        Feature::define('pro-features', fn (): bool => tenant()?->plan?->meetsRequirement(SubscriptionTier::Pro) ?? false);

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => view('filament.render-hooks.sidebar-and-autofill')->render(),
        );

        // Add tenancy middleware to Livewire's update endpoint
        // Without this, Livewire POSTs (login, forms) hit the central DB
        Livewire::addPersistentMiddleware([
            InitializeTenancyByDomainOrSubdomain::class,
        ]);
    }
}
