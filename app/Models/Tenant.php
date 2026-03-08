<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

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
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'storefront_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get or generate a unique referral code for this tenant.
     */
    public function getReferralCodeAttribute(): string
    {
        $referral = Referral::where('referrer_tenant_id', $this->id)
            ->whereNull('referred_tenant_id')
            ->whereNull('referred_email')
            ->first();

        if (! $referral) {
            $code = Str::slug($this->store_name ?? $this->name) . '-' . Str::lower(Str::random(4));

            // Ensure uniqueness
            while (Referral::where('referral_code', $code)->exists()) {
                $code = Str::slug($this->store_name ?? $this->name) . '-' . Str::lower(Str::random(4));
            }

            $referral = Referral::create([
                'referrer_tenant_id' => $this->id,
                'referral_code' => $code,
                'status' => 'pending',
            ]);
        }

        return $referral->referral_code;
    }

    /**
     * Referrals made by this tenant.
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
        return Referral::where('referred_tenant_id', $this->id)->first();
    }
}
