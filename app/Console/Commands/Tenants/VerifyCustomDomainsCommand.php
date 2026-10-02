<?php

namespace App\Console\Commands\Tenants;

use App\Actions\Platform\VerifyCustomDomain;
use App\Models\Platform\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('tenants:verify-custom-domains')]
#[Description('Re-check DNS for every bakery custom domain and record whether it is verified')]
class VerifyCustomDomainsCommand extends Command
{
    public function handle(VerifyCustomDomain $verify): int
    {
        $checked = 0;
        $verified = 0;
        $changed = 0;

        foreach (Tenant::query()->whereNotNull('custom_domain')->where('custom_domain', '!=', '')->cursor() as $tenant) {
            $wasVerified = $tenant->custom_domain_verified_at !== null;
            $isVerified = $verify($tenant);

            $checked++;
            $verified += (int) $isVerified;

            if ($wasVerified === $isVerified) {
                continue;
            }

            $changed++;

            $context = ['tenant' => $tenant->id, 'domain' => $tenant->custom_domain];

            if ($isVerified) {
                Log::info('Custom domain verified.', $context);

                continue;
            }

            Log::warning('Custom domain no longer verified.', $context);
        }

        $unverified = $checked - $verified;

        $this->info("Custom domains checked: {$checked}; verified: {$verified}; unverified: {$unverified}; changed: {$changed}.");

        return self::SUCCESS;
    }
}
