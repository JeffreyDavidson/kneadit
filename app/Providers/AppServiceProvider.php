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
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsManager::class);
        $this->app->singleton(PlatformSettingsManager::class);
        $this->app->singleton(TenantSettingsRegistry::class);
        $this->app->singleton(TenantSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->all());

        // Each sub-DTO is directly injectable for focused services
        $this->app->bind(StoreInfo::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->store());
        $this->app->bind(BrandingSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->branding());
        $this->app->bind(OrderSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->orders());
        $this->app->bind(PaymentSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->payment());
        $this->app->bind(LoyaltySettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->loyalty());
        $this->app->bind(CateringSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->catering());
        $this->app->bind(EngagementSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->engagement());
        $this->app->bind(PolicySettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->policies());
        $this->app->bind(WebhookSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->webhooks());
        $this->app->bind(HomepageSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->homepage());
        $this->app->bind(OnboardingSettings::class, fn (Application $app) => $app->make(TenantSettingsRegistry::class)->onboarding());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('webhooks', fn () => Limit::perMinute(30));

        Gate::define('platform-admin', fn (User $user): bool => $user->role === UserRole::PlatformAdmin);

        Feature::define('growth-features', fn (): bool => SubscriptionTier::tryFrom(tenant()?->plan)?->meetsRequirement(SubscriptionTier::Growth) ?? false);
        Feature::define('pro-features', fn (): bool => SubscriptionTier::tryFrom(tenant()?->plan)?->meetsRequirement(SubscriptionTier::Pro) ?? false);
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => Blade::render(<<<'HTML'
                <script>
                    document.addEventListener("livewire:navigating", () => {
                        const sidebar = document.querySelector(".fi-sidebar-nav");
                        if (sidebar) window.__sidebarScroll = sidebar.scrollTop;
                    });
                    document.addEventListener("livewire:navigated", () => {
                        const sidebar = document.querySelector(".fi-sidebar-nav");
                        if (sidebar && window.__sidebarScroll) sidebar.scrollTop = window.__sidebarScroll;
                    });
                    // Disable 1Password/autofill on all Filament form fields
                    function disable1Password() {
                        document.querySelectorAll('.fi-fo-field-wrp input, .fi-fo-field-wrp textarea, .fi-fo-field-wrp select').forEach(el => {
                            el.setAttribute('autocomplete', 'off');
                            el.setAttribute('data-1p-ignore', '');
                            el.setAttribute('data-lpignore', 'true');
                            el.setAttribute('data-form-type', 'other');
                        });
                    }
                    document.addEventListener('livewire:navigated', disable1Password);
                    document.addEventListener('livewire:morph', disable1Password);
                    setTimeout(disable1Password, 500);
                </script>
            HTML),
        );

        // Add tenancy middleware to Livewire's update endpoint
        // Without this, Livewire POSTs (login, forms) hit the central DB
        Livewire::addPersistentMiddleware([
            InitializeTenancyByDomainOrSubdomain::class,
        ]);
    }
}
