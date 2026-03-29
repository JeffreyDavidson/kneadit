<?php

namespace App\Providers;

use App\Enums\SubscriptionTier;
use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use App\View\Composers\StorefrontComposer;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('webhooks', fn () => Limit::perMinute(30));

        View::composer('layouts.storefront', StorefrontComposer::class);

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
