<?php

namespace App\Models;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserRole $role
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User hasExpiredGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'central') {
            return $this->role === UserRole::PlatformAdmin;
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
        return $this->role === UserRole::Owner;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function hasMinRole(UserRole $role): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->meetsRequirement($role);
    }
}
