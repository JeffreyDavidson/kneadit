<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Observers\OrderObserver;
use App\Observers\SupportTicketObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Order::observe(OrderObserver::class);
        SupportTicket::observe(SupportTicketObserver::class);

        // Add tenancy middleware to Livewire's update endpoint
        // Without this, Livewire POSTs (login, forms) hit the central DB
        Livewire::addPersistentMiddleware([
            InitializeTenancyByDomainOrSubdomain::class,
        ]);

        // Debug: log auth attempts on tenant subdomains
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            $manualCheck = $event->user ? \Hash::check($event->credentials['password'] ?? '', $event->user->password) : false;
            $panel = \Filament\Facades\Filament::getCurrentOrDefaultPanel();
            \Log::warning('AUTH FAILED', [
                'tenant' => tenant()?->id,
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? 'n/a',
                'db' => \DB::connection()->getDatabaseName(),
                'user_found' => $event->user ? 'yes' : 'no',
                'manual_hash_check' => $manualCheck ? 'PASS' : 'FAIL',
                'panel_id' => $panel?->getId(),
                'user_role' => $event->user?->role ?? 'n/a',
                'canAccessPanel' => ($event->user && $panel) ? ($event->user->canAccessPanel($panel) ? 'YES' : 'NO') : 'n/a',
            ]);
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Attempting::class, function ($event) {
            \Log::info('AUTH ATTEMPTING', [
                'tenant' => tenant()?->id,
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? 'n/a',
                'db' => \DB::connection()->getDatabaseName(),
            ]);
        });
    }
}
