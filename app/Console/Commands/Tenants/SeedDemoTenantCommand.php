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

        // Register both the full hostname AND the bare subdomain so the tenant
        // resolves regardless of how stancl/tenancy's identification middleware
        // is currently configured. Stancl's InitializeTenancyByDomainOrSubdomain
        // checks if the hostname ends with a central_domain — when true (which
        // it is for *.kneadit.test) it falls through to subdomain identification
        // and looks up `domain = '<bare-subdomain>'`, NOT the full hostname.
        // Matches the pattern in CreateOneTenantCommand used by kneadit:seed-local.
        $centralDomains = config('tenancy.central_domains');
        $centralDomain = is_array($centralDomains) ? (string) ($centralDomains[0] ?? '') : '';
        $tenant->domains()->updateOrCreate(['domain' => self::DEMO_ID . '.' . $centralDomain]);
        $tenant->domains()->updateOrCreate(['domain' => self::DEMO_ID]);

        $this->info('✅ Demo tenant ready at https://' . self::DEMO_ID . '.' . $centralDomain);

        return self::SUCCESS;
    }
}
