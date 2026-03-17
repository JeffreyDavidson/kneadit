<?php

namespace App\Models;

use App\Enums\SubscriptionTier;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use Billable, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'central') {
            return $this->role === 'platform_admin';
        }

        return true;
    }

    /**
     * Get the user's current subscription plan key.
     */
    public function currentPlan(): ?string
    {
        $subscription = $this->subscription('default');

        if (! $subscription) {
            return null;
        }

        $priceId = $subscription->stripe_price;

        return match ($priceId) {
            config('saas.stripe_prices.starter') => 'starter',
            config('saas.stripe_prices.growth') => 'growth',
            config('saas.stripe_prices.pro') => 'pro',
            default => null,
        };
    }

    /**
     * Check if user has at least the given plan tier.
     */
    public function hasPlan(string $plan): bool
    {
        $current = SubscriptionTier::tryFrom($this->currentPlan() ?? '');
        $required = SubscriptionTier::tryFrom($plan);

        if (! $current || ! $required) {
            return false;
        }

        return $current->meetsRequirement($required);
    }

    /**
     * Check if user has an active subscription or is on trial.
     */
    public function hasAccess(): bool
    {
        return $this->subscribed('default') || $this->onTrial();
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function hasMinRole(string $role): bool
    {
        $hierarchy = ['staff' => 1, 'manager' => 2, 'owner' => 3];

        return ($hierarchy[$this->role] ?? 0) >= ($hierarchy[$role] ?? 0);
    }
}
