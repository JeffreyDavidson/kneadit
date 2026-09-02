<?php

namespace App\Console\Commands\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Database\Models\Domain;

#[Signature('tenants:seed-demo {--fresh : Drop and recreate if the demo tenant already exists}')]
#[Description("Provision a 'demo' tenant seeded with the onboarded() factory state — used by the central WidgetCatalog page for design preview")]
class SeedDemoTenantCommand extends Command
{
    public function handle(TenantSQLiteDatabaseManager $manager): int
    {
        $existing = Tenant::query()->find(Tenant::DEMO_ID);

        if ($existing && ! $this->option('fresh')) {
            $this->info("Demo tenant '" . Tenant::DEMO_ID . "' already exists. Pass --fresh to recreate.");

            return self::SUCCESS;
        }

        if ($existing) {
            $this->info("Recreating demo tenant '" . Tenant::DEMO_ID . "'…");
            $manager->deleteDatabase($existing);
            $existing->delete();
        } else {
            $this->info("Creating demo tenant '" . Tenant::DEMO_ID . "'…");
        }

        $tenant = Tenant::factory()
            ->onboarded()
            ->create([
                'id' => Tenant::DEMO_ID,
                'name' => 'Demo Owner',
                'email' => 'demo@getkneadit.app',
                'store_name' => 'Demo Bakery',
                'is_demo' => true,
            ]);

        // Register both the full hostname AND the bare subdomain so the tenant
        // resolves regardless of how stancl/tenancy's identification middleware
        // is currently configured. Stancl's InitializeTenancyByDomainOrSubdomain
        // checks if the hostname ends with a central_domain — when true (which
        // it is for *.kneadit.test) it falls through to subdomain identification
        // and looks up `domain = '<bare-subdomain>'`, NOT the full hostname.
        // Matches the pattern in CreateOneTenantCommand used by kneadit:seed-local.
        $centralDomains = Config::array('tenancy.central_domains', []);
        $centralDomain = is_string($centralDomains[0] ?? null) ? $centralDomains[0] : '';
        Domain::query()->updateOrCreate(['domain' => Tenant::DEMO_ID . '.' . $centralDomain], ['tenant_id' => $tenant->id]);
        Domain::query()->updateOrCreate(['domain' => Tenant::DEMO_ID], ['tenant_id' => $tenant->id]);

        $this->info('✅ Demo tenant ready at https://' . Tenant::DEMO_ID . '.' . $centralDomain);

        return self::SUCCESS;
    }
}
