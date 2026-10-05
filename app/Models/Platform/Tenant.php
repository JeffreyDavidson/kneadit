<?php

namespace App\Models\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Customers\Referral;
use App\Models\Staff\User;
use Database\Factories\Platform\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string $name
 * @property string $email
 * @property SubscriptionTier $plan
 * @property bool $free_forever
 * @property Carbon|null $trial_ends_at
 * @property string|null $store_name
 * @property string|null $store_logo
 * @property string $brand_color_primary
 * @property string $brand_color_secondary
 * @property bool $storefront_enabled
 * @property Carbon|null $paused_at
 * @property-read bool $is_paused
 * @property-read bool $storefront_set_up
 * @property string|null $external_website
 * @property bool $is_active
 * @property bool $is_demo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $onboarding_products_count
 * @property int $onboarding_categories_count
 * @property int $onboarding_orders_count
 * @property Carbon|null $onboarding_metrics_synced_at
 * @property array<array-key, mixed>|null $data
 * @property string|null $custom_domain
 * @property Carbon|null $custom_domain_verified_at
 * @property-read User|null $owner
 * @property-read Collection<int, Domain> $domains
 * @property-read int|null $domains_count
 * @property-read Collection<int, TenantNote> $notes
 * @property-read int|null $notes_count
 * @property-read Collection<int, Referral> $referralsMade
 * @property-read int|null $referrals_made_count
 * @property-read Referral|null $referral
 *
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 *
 * @property Carbon|null $last_login_at
 *
 * @mixin \Eloquent
 */
#[UseFactory(TenantFactory::class)]
class Tenant extends BaseTenant implements TenantWithDatabase
{
    public const string DEMO_ID = 'demo';

    use HasDatabase, HasDomains;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /**
     * Custom columns on the tenants table.
     */
    /** @return array<int, string> */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'user_id',
            'name',
            'email',
            'plan',
            'free_forever',
            'trial_ends_at',
            'store_name',
            'store_logo',
            'brand_color_primary',
            'brand_color_secondary',
            'storefront_enabled',
            'paused_at',
            'external_website',
            'is_active',
            'is_demo',
            'custom_domain',
            'custom_domain_verified_at',
            'last_login_at',
            'onboarding_products_count',
            'onboarding_categories_count',
            'onboarding_orders_count',
            'onboarding_metrics_synced_at',
        ];
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'plan' => SubscriptionTier::class,
            'free_forever' => 'boolean',
            'trial_ends_at' => 'datetime',
            'storefront_enabled' => 'boolean',
            'paused_at' => 'datetime',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
            'custom_domain_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'onboarding_products_count' => 'integer',
            'onboarding_categories_count' => 'integer',
            'onboarding_orders_count' => 'integer',
            'onboarding_metrics_synced_at' => 'datetime',
        ];
    }

    /**
     * The central user account that owns this bakery. Null for demo and
     * platform tenants, and for older bakeries that could not be matched.
     * The related query inherits this model's central connection.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A paused bakery takes no new orders, shows a paused page on its KneadIt
     * storefront and gets no customer-facing scheduled emails. It is separate
     * from storefront_enabled, which only says whether the bakery uses a
     * KneadIt storefront at all.
     *
     * @return Attribute<bool, never>
     */
    protected function isPaused(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->paused_at !== null,
        );
    }

    /**
     * Whether the storefront onboarding step is done: the bakery either uses a
     * KneadIt storefront or has said it runs its own website.
     *
     * @return Attribute<bool, never>
     */
    protected function storefrontSetUp(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->storefront_enabled || filled($this->external_website),
        );
    }

    /**
     * Notes for this tenant.
     *
     * @return HasMany<TenantNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(TenantNote::class);
    }

    /** @return HasMany<FreeForeverGrant, $this> */
    public function freeForeverGrants(): HasMany
    {
        return $this->hasMany(FreeForeverGrant::class);
    }

    /**
     * Referrals made by this tenant.
     *
     * @return HasMany<Referral, $this>
     */
    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_tenant_id');
    }

    /**
     * The referral that brought this tenant in, if any.
     *
     * @return HasOne<Referral, $this>
     */
    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_tenant_id');
    }
}
