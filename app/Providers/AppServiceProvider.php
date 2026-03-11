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
            \Log::warning('AUTH FAILED', [
                'tenant' => tenant()?->id,
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? 'n/a',
                'db' => \DB::connection()->getDatabaseName(),
                'user_found' => $event->user ? 'yes' : 'no',
                'user_class' => $event->user ? get_class($event->user) : 'n/a',
                'manual_hash_check' => $manualCheck ? 'PASS' : 'FAIL',
                'hash_prefix' => $event->user ? substr($event->user->password, 0, 10) : 'n/a',
                'password_length' => strlen($event->credentials['password'] ?? ''),
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
