<?php

namespace App\Console\Commands\Customers;

use App\Actions\Customers\GenerateCustomerReferralCode;
use App\Models\Customers\Customer;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('customers:backfill-referral-codes')]
#[Description('Generate referral codes for any existing customers that pre-date the referral feature')]
class BackfillReferralCodesCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        $totalGenerated = 0;

        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings) use (&$totalGenerated): void {
                $generator = resolve(GenerateCustomerReferralCode::class);
                $count = 0;

                Customer::query()
                    ->whereNull('referral_code')
                    ->cursor()
                    ->each(function (Customer $customer) use ($generator, &$count): void {
                        $generator($customer);
                        $count++;
                    });

                if ($count > 0) {
                    $this->info("{$tenant->id}: backfilled {$count} customers");
                    $totalGenerated += $count;
                }
            },
        );

        $this->info("Total customers updated: {$totalGenerated}");

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
