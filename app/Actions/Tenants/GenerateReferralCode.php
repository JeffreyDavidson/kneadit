<?php

namespace App\Actions\Tenants;

use App\Enums\Customers\ReferralStatus;
use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;
use Illuminate\Support\Str;

class GenerateReferralCode
{
    public function __invoke(Tenant $tenant): string
    {
        $referral = Referral::query()
            ->where('referrer_tenant_id', $tenant->id)
            ->whereNull('referred_tenant_id')
            ->whereNull('referred_email')
            ->first();

        if (! $referral) {
            $code = Str::slug($tenant->store_name ?? $tenant->name) . '-' . Str::lower(Str::random(4));

            while (Referral::query()->where('referral_code', $code)->exists()) {
                $code = Str::slug($tenant->store_name ?? $tenant->name) . '-' . Str::lower(Str::random(4));
            }

            $referral = Referral::query()->create([
                'referrer_tenant_id' => $tenant->id,
                'referral_code' => $code,
                'status' => ReferralStatus::Pending,
            ]);
        }

        return $referral->referral_code;
    }
}
