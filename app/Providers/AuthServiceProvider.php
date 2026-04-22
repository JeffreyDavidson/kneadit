<?php

namespace App\Providers;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('platform-admin', fn (User $user): bool => $user->role === UserRole::PlatformAdmin);

        Gate::define('has-plan', fn (User $user, SubscriptionTier $tier): bool => SubscriptionTier::resolve($user)?->meetsRequirement($tier) ?? false);

        // Send unauthenticated storefront customers to their own login page instead of
        // the platform /login (which is for bakery staff).
        Authenticate::redirectUsing(
            fn (Request $request): string => $request->is('account*') ? route('account.login.show') : route('login'),
        );

        Event::listen(Authenticated::class, function (Authenticated $event): void {
            if ($event->user instanceof User) {
                ActorContext::set($event->user);
            }
        });
    }
}
