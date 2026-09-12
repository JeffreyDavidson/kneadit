<?php

namespace App\Console\Commands\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;

#[Signature('tenants:provision-test-tenant {--fresh : Drop and recreate the browser-test tenant} {--domain=browser-test.kneadit.test : Domain used for the browser-test tenant}')]
#[Description('Provision the canonical browser-test tenant (tenant + domain + DB + migrations + BrowserTestFixtureSeeder)')]
class ProvisionTestTenantCommand extends Command
{
    public const string TENANT_ID = 'browser-test';

    public const string STORE_NAME = 'Browser Test Bakery';

    public function handle(TenantSQLiteDatabaseManager $manager): int
    {
        if ($this->option('fresh')) {
            $this->deleteOrphanedDatabase($manager);
        }

        if ($this->option('fresh') && Tenant::query()->whereKey(self::TENANT_ID)->exists()) {
            $this->info('Dropping existing browser-test tenant...');
            Tenant::query()->whereKey(self::TENANT_ID)->first()?->delete();
        }

        if (Tenant::query()->whereKey(self::TENANT_ID)->exists()) {
            $this->warn('browser-test tenant already exists. Re-running fixture seeder only.');
            $tenant = Tenant::query()->findOrFail(self::TENANT_ID);
        } else {
            $this->info('Creating browser-test tenant + domain + DB...');
            $tenant = $this->createTenant();
        }

        $this->info('Seeding BrowserTestFixtureSeeder...');
        Artisan::call('tenants:seed', [
            '--tenants' => [self::TENANT_ID],
            '--class' => \Database\Seeders\BrowserTestFixtureSeeder::class,
            '--force' => true,
        ]);

        $this->newLine();
        $this->info("✅ browser-test tenant ready at http://{$this->domain()}");

        if (filter_var(trim($this->domain(), '[]'), FILTER_VALIDATE_IP) === false) {
            $this->newLine();
            $this->warn('Add to /etc/hosts if not already present:');
            $this->line("  127.0.0.1  {$this->domain()}");
        }

        return self::SUCCESS;
    }

    private function createTenant(): Tenant
    {
        $tenant = Tenant::query()->create([
            'id' => self::TENANT_ID,
            'name' => 'Browser Test Owner',
            'email' => 'browser-test-owner@kneadit.test',
            'plan' => SubscriptionTier::Pro,
            'trial_ends_at' => now()->addDays(365),
            'store_name' => self::STORE_NAME,
            'brand_color_primary' => '#d4920c',
            'brand_color_secondary' => '#1c1410',
            'is_active' => true,
            'storefront_enabled' => true,
        ]);

        Domain::query()->create(['domain' => $this->domain(), 'tenant_id' => $tenant->id]);
        Domain::query()->create(['domain' => self::TENANT_ID, 'tenant_id' => $tenant->id]);

        Artisan::call('tenants:migrate', [
            '--tenants' => [self::TENANT_ID],
            '--force' => true,
        ]);

        $tenant->run(function () use ($tenant) {
            DB::connection('tenant')->table('users')->insert([
                'name' => $tenant->name,
                'email' => $tenant->email,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            resolve(SettingsManager::class)->setMany([
                'store_name' => $tenant->store_name,
                'store_email' => $tenant->email,
            ]);

            Artisan::call('db:seed', ['--force' => true]);
        });

        return $tenant;
    }

    private function domain(): string
    {
        return (string) $this->option('domain');
    }

    private function deleteOrphanedDatabase(TenantSQLiteDatabaseManager $manager): void
    {
        if (Tenant::query()->whereKey(self::TENANT_ID)->exists()) {
            return;
        }

        $tenant = new Tenant(['id' => self::TENANT_ID]);
        $databaseName = $tenant->database()->getName();

        if ($databaseName === null || ! $manager->databaseExists($databaseName)) {
            return;
        }

        $this->info('Dropping orphaned browser-test tenant database...');
        $manager->deleteDatabase($tenant);
    }
}
