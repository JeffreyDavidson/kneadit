<?php

namespace App\Console\Commands\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:seed-demo {--fresh : Drop and recreate if the demo tenant already exists}')]
#[Description("Provision a 'demo' tenant seeded with the onboarded() factory state — used by the central WidgetCatalog page for design preview")]
class SeedDemoTenantCommand extends Command
{
    public const string DEMO_ID = 'demo';

    public function handle(TenantSQLiteDatabaseManager $manager): int
    {
        $existing = Tenant::query()->find(self::DEMO_ID);

        if ($existing && ! $this->option('fresh')) {
            $this->info("Demo tenant '" . self::DEMO_ID . "' already exists. Pass --fresh to recreate.");

            return self::SUCCESS;
        }

        if ($existing) {
            $this->info("Recreating demo tenant '" . self::DEMO_ID . "'…");
            $manager->deleteDatabase($existing);
            $existing->delete();
        } else {
            $this->info("Creating demo tenant '" . self::DEMO_ID . "'…");
        }

        $tenant = Tenant::factory()
            ->onboarded()
            ->create([
                'id' => self::DEMO_ID,
                'name' => 'Demo Owner',
                'email' => 'demo@getkneadit.app',
                'store_name' => 'Demo Bakery',
            ]);

        // Register the subdomain so requests to demo.<central-domain>
        // resolve to this tenant via stancl/tenancy's domain identification.
        $centralDomain = (string) collect(config('tenancy.central_domains'))->first();
        $tenant->domains()->updateOrCreate(['domain' => self::DEMO_ID . '.' . $centralDomain]);

        $this->info('✅ Demo tenant ready at https://' . self::DEMO_ID . '.' . $centralDomain);

        return self::SUCCESS;
    }
}
