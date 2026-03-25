<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $plan
 * @property Carbon|null $trial_ends_at
 * @property string|null $store_name
 * @property string|null $store_logo
 * @property string $brand_color_primary
 * @property string $brand_color_secondary
 * @property bool $storefront_enabled
 * @property string|null $external_website
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $data
 * @property string|null $custom_domain
 * @property-read Collection<int, Domain> $domains
 * @property-read int|null $domains_count
 * @property-read Collection<int, TenantNote> $notes
 * @property-read int|null $notes_count
 * @property-read Collection<int, Referral> $referralsMade
 * @property-read int|null $referrals_made_count
 *
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Database\Factories\TenantFactory factory($count = null, $state = [])
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBrandColorPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereBrandColorSecondary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCustomDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereExternalWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStoreLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStoreName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStorefrontEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
 *
 * @property Carbon|null $last_login_at
 *
 * @mixin \Eloquent
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    use HasFactory;

    /**
     * Custom columns on the tenants table.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'plan',
            'trial_ends_at',
            'store_name',
            'store_logo',
            'brand_color_primary',
            'brand_color_secondary',
            'storefront_enabled',
            'external_website',
            'is_active',
            'custom_domain',
            'last_login_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'storefront_enabled' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Find an existing referral code or create a new one for this tenant.
     */
    public function findOrCreateReferralCode(): string
    {
        $referral = Referral::query()->where('referrer_tenant_id', $this->id)
            ->whereNull('referred_tenant_id')
            ->whereNull('referred_email')
            ->first();

        if (! $referral) {
            $code = Str::slug($this->store_name ?? $this->name).'-'.Str::lower(Str::random(4));

            // Ensure uniqueness
            while (Referral::query()->where('referral_code', $code)->exists()) {
                $code = Str::slug($this->store_name ?? $this->name).'-'.Str::lower(Str::random(4));
            }

            $referral = Referral::query()->create([
                'referrer_tenant_id' => $this->id,
                'referral_code' => $code,
                'status' => ReferralStatus::Pending,
            ]);
        }

        return $referral->referral_code;
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
     * Get the referral that brought this tenant in, if any.
     */
    public function referredBy(): ?Referral
    {
        return Referral::query()->where('referred_tenant_id', $this->id)->first();
    }
}
