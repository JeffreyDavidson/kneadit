<?php

namespace App\Providers;

use App\Enums\Platform\SubscriptionTier;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Feature::define('growth-features', fn (): bool => $this->tenantMeetsRequirement(SubscriptionTier::Growth));
        Feature::define('pro-features', fn (): bool => $this->tenantMeetsRequirement(SubscriptionTier::Pro));

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

    private function tenantMeetsRequirement(SubscriptionTier $required): bool
    {
        $tenant = app()->bound(\Stancl\Tenancy\Contracts\Tenant::class)
            ? app(\Stancl\Tenancy\Contracts\Tenant::class)
            : tenancy()->tenant;
        $plan = data_get($tenant, 'plan');

        return $plan instanceof SubscriptionTier && $plan->meetsRequirement($required);
    }
}
