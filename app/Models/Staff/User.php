<?php

namespace App\Models\Staff;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Models\Platform\Tenant;
use Database\Factories\Staff\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;
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
 * @property-read bool $is_owner
 * @property-read bool $is_manager
 * @property-read bool $is_staff
 * @property-read bool $has_access
 * @property-read SubscriptionTier|null $current_plan
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 *
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
#[Fillable('name', 'email', 'password', 'role')]
#[Hidden('password', 'remember_token')]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable;

    public const string CENTRAL_PANEL_ID = 'central';

    public static function passwordRule(): Password
    {
        return Password::min(8)->letters()->numbers();
    }

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
        if ($panel->getId() === self::CENTRAL_PANEL_ID) {
            return $this->role === UserRole::PlatformAdmin;
        }

        return true;
    }

    /** @return Attribute<bool, never> */
    protected function isOwner(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->role === UserRole::Owner,
        );
    }

    /** @return Attribute<bool, never> */
    protected function isManager(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->role === UserRole::Manager,
        );
    }

    /** @return Attribute<bool, never> */
    protected function isStaff(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->role === UserRole::Staff,
        );
    }

    /** @return Attribute<SubscriptionTier|null, never> */
    protected function currentPlan(): Attribute
    {
        return Attribute::make(
            get: function (): ?SubscriptionTier {
                $priceId = $this->subscription('default')?->stripe_price;

                return $priceId ? SubscriptionTier::fromPriceId($priceId) : null;
            },
        );
    }

    /** @return Attribute<bool, never> */
    protected function hasAccess(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->subscribed('default') || $this->onTrial(),
        );
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
