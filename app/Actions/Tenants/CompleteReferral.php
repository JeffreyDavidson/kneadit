<?php

namespace App\Actions\Tenants;

use App\Enums\ReferralStatus;
use App\Models\Referral;

class CompleteReferral
{
    public function __invoke(?string $referralCode, string $tenantId, string $email): void
    {
        if (! $referralCode) {
            return;
        }

        $referral = Referral::query()->where('referral_code', $referralCode)
            ->where('status', ReferralStatus::Pending)
            ->whereNull('referred_tenant_id')
            ->first();

        if (! $referral) {
            return;
        }

        $referral->update([
            'referred_tenant_id' => $tenantId,
            'referred_email' => $email,
            'status' => ReferralStatus::Completed,
        ]);
    }
}
