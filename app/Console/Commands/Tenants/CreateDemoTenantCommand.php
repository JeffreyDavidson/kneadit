<?php

namespace App\Console\Commands\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

#[Signature('tenant:demo {--fresh : Drop and recreate the demo tenant}')]
#[Description('Create a demo tenant for local testing')]
class CreateDemoTenantCommand extends Command
{
    public function handle(): int
    {
        $subdomain = 'demo';
        $domain = 'demo.kneadit.test';

        if ($this->option('fresh')) {
            $existing = Tenant::query()->find($subdomain);
            if ($existing) {
                $this->info('Deleting existing demo tenant...');
                $existing->delete();
            }

            // Fallback: remove SQLite tenant DB file if it still exists
            $dbFile = database_path("tenant{$subdomain}.sqlite");
            if (file_exists($dbFile)) {
                unlink($dbFile);
                $this->info('Removed leftover database file.');
            }
        }

        if (Tenant::query()->find($subdomain)) {
            $this->warn('Demo tenant already exists. Use --fresh to recreate.');

            return self::SUCCESS;
        }

        $this->info('Creating demo tenant...');

        // Create tenant (this triggers CreateDatabase + MigrateDatabase via events)
        $tenant = Tenant::query()->create([
            'id' => $subdomain,
            'name' => 'Demo Baker',
            'email' => 'demo@getkneadit.app',
            'plan' => SubscriptionTier::Pro,
            'trial_ends_at' => now()->addDays(Config::integer('kneadit.trial_days', 30)),
            'store_name' => 'Sweet Dreams Bakery',
            'brand_color_primary' => '#d4920c',
            'brand_color_secondary' => '#1c1410',
            'is_active' => true,
        ]);

        $tenant->domains()->create(['domain' => $domain]);
        $tenant->domains()->create(['domain' => $subdomain]);

        // Verify tenant database was created
        $dbName = 'tenant' . $subdomain;
        $dbPath = database_path($dbName);

        if (! file_exists($dbPath)) {
            $this->warn('Tenant database not auto-created. Creating manually...');
            touch($dbPath);
        }

        $this->info('Running tenant migrations...');

        // Manually run tenant migrations
        Artisan::call('tenants:migrate', [
            '--tenants' => [$subdomain],
            '--force' => true,
        ]);
        $this->info(Artisan::output());

        $this->info('Seeding tenant data...');

        $tenant->run(function () {
            // Create admin user
            User::query()->create([
                'name' => 'Demo Baker',
                'email' => 'demo@getkneadit.app',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Seed settings
            resolve(SettingsManager::class)->setMany([
                'store_name' => 'Sweet Dreams Bakery',
                'store_email' => 'demo@getkneadit.app',
                'store_phone' => '(863) 555-0123',
                'store_address' => '123 Main Street, Davenport, FL 33837',
                'default_daily_capacity' => '15',
            ]);

            // Run seeders
            Artisan::call('db:seed', ['--force' => true]);
        });

        $this->newLine();
        $this->info('✅ Demo tenant created!');
        $this->newLine();
        $this->table(['', ''], [
            ['Storefront', "http://{$domain}"],
            ['Admin Panel', "http://{$domain}/admin"],
            ['Email', 'demo@getkneadit.app'],
            ['Password', 'password'],
            ['Plan', 'Pro (30-day trial)'],
        ]);

        $this->newLine();
        $this->warn("Make sure '{$domain}' resolves to 127.0.0.1 in your /etc/hosts file:");
        $this->info("  127.0.0.1  {$domain}");

        return self::SUCCESS;
    }
}
