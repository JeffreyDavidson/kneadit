<?php

namespace App\Providers;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Http\Middleware\AuthenticateCustomerSession;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use App\Support\Auth\NormalizedEmailUserProvider;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Staff sign-in and password reset find users by email regardless of case.
        auth()->provider('normalized-email', fn (Application $app, array $config): NormalizedEmailUserProvider => new NormalizedEmailUserProvider($app->make('hash'), Arr::string($config, 'model')));

        Gate::define('platform-admin', fn (User $user): bool => $user->role === UserRole::PlatformAdmin);

        Gate::define('manager-staff', fn (User $user): bool => $user->role->meetsRequirement(UserRole::Manager));

        // Webhooks carry customer names and emails to an external URL, so only
        // the bakery owner may configure, test or inspect them.
        Gate::define('manage-webhooks', fn (User $user): bool => $user->role->meetsRequirement(UserRole::Owner));

        // Billing is the bakery owner's; the handoff signs them in to the central
        // billing pages as the account that owns the bakery.
        Gate::define('manage-billing', fn (User $user): bool => $user->role->meetsRequirement(UserRole::Owner));

        Gate::define('has-plan', fn (User $user, SubscriptionTier $tier): bool => SubscriptionTier::resolve($user)?->meetsRequirement($tier) ?? false);

        // Send signed-out visitors to the right sign-in: customers to the storefront login,
        // drivers (bakery staff) to the bakery admin, everyone else to the platform /login.
        Authenticate::redirectUsing(fn (Request $request): string => match (true) {
            $request->is('account*') => route('account.login.show'),
            $request->is('driver*') => route('filament.admin.auth.login'),
            default => route('login'),
        });

        // Record the password a customer signed in with (including a remember-cookie login), so
        // AuthenticateCustomerSession can end the session if the password is changed afterwards.
        // Recording it at sign-in rather than on the next page means a session that is never
        // used before a reset still dies with it.
        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard === 'customer') {
                session()->put(AuthenticateCustomerSession::SESSION_KEY, AuthenticateCustomerSession::passwordHash($event->user));
            }
        });

        Event::listen(Authenticated::class, function (Authenticated $event): void {
            if ($event->user instanceof User) {
                ActorContext::set($event->user);
            }
        });
    }
}
