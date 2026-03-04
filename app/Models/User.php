<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements FilamentUser
{
    use Billable, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
            env('STRIPE_PRICE_STARTER') => 'starter',
            env('STRIPE_PRICE_GROWTH') => 'growth',
            env('STRIPE_PRICE_PRO') => 'pro',
            default => null,
        };
    }

    /**
     * Check if user has at least the given plan tier.
     */
    public function hasPlan(string $plan): bool
    {
        $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];
        $currentLevel = $hierarchy[$this->currentPlan()] ?? 0;
        $requiredLevel = $hierarchy[$plan] ?? 0;

        return $currentLevel >= $requiredLevel;
    }

    /**
     * Check if user has an active subscription or is on trial.
     */
    public function hasAccess(): bool
    {
        return $this->subscribed('default') || $this->onTrial();
    }
}
