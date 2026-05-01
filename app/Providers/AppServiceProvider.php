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

        // Centralized Stripe client so Stripe-using actions can be tested with
        // a mocked binding rather than instantiating the client themselves.
        $this->app->bind(\Stripe\StripeClient::class, fn () => new \Stripe\StripeClient(config('cashier.secret')));

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

        // Named throttle limiters used by route definitions. Naming the
        // intent (sensitive-write, form-write, etc.) instead of repeating
        // raw `throttle:5,1` / `throttle:10,1` everywhere makes route
        // files readable and lets the browser-test bypass live in one
        // place rather than as a dedicated middleware subclass.
        $key = fn (\Illuminate\Http\Request $request): string => $request->user()?->getAuthIdentifier() !== null
            ? (string) $request->user()->getAuthIdentifier()
            : (string) $request->ip();

        $bypass = fn (\Illuminate\Http\Request $request): bool => $request->getHost() === 'browser-test.kneadit.test';

        // Auth + payment + order-modify + invite + central marketing contact.
        RateLimiter::for('sensitive-write', fn (\Illuminate\Http\Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(5)->by($key($request)));

        // Verification email resend — intentionally slow; spamming the
        // inbox is the abuse pattern this guards against.
        RateLimiter::for('verification-resend', fn (\Illuminate\Http\Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(6)->by($key($request)));

        // Storefront forms + order writes + API writes — the catch-all
        // for "user just submitted a form, allow a few retries on hiccups."
        RateLimiter::for('form-write', fn (\Illuminate\Http\Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(10)->by($key($request)));

        // Referral code claim — bumped above form-write because users
        // legitimately retry with multiple variations and the abuse cost
        // is low (claim is idempotent against a known set of codes).
        RateLimiter::for('referral-claim', fn (\Illuminate\Http\Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(30)->by($key($request)));

        // High-frequency reads + light passive writes (cart persist,
        // pickup-slots lookup, CSP-report intake, public API reads).
        RateLimiter::for('frequent-poll', fn (\Illuminate\Http\Request $request) => $bypass($request)
            ? Limit::none()
            : Limit::perMinute(60)->by($key($request)));

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
