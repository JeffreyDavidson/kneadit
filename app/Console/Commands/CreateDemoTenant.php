<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateDemoTenant extends Command
{
    protected $signature = 'tenant:demo {--fresh : Drop and recreate the demo tenant}';
    protected $description = 'Create a demo tenant for local testing';

    public function handle(): int
    {
        $subdomain = 'demo';
        $domain = 'demo.kneadit.test';

        // Check if demo tenant already exists
        $existing = Tenant::find($subdomain);

        if ($existing) {
            if ($this->option('fresh')) {
                $this->info('Deleting existing demo tenant...');
                $existing->delete();
            } else {
                $this->warn('Demo tenant already exists. Use --fresh to recreate.');
                $this->info("Access it at: http://{$domain}");
                $this->info("Admin panel: http://{$domain}/admin");
                return self::SUCCESS;
            }
        }

        $this->info('Creating demo tenant...');

        $tenant = Tenant::create([
            'id' => $subdomain,
            'name' => 'Demo Baker',
            'email' => 'demo@getkneadit.app',
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(30),
            'store_name' => 'Sweet Dreams Bakery',
            'brand_color_primary' => '#d4920c',
            'brand_color_secondary' => '#1c1410',
            'is_active' => true,
        ]);

        // Store both the full domain and just the subdomain for flexible resolution
        $tenant->domains()->create([
            'domain' => $domain,
        ]);
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        $this->info('Running tenant migrations...');

        $tenant->run(function () {
            // Run migrations
            \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Create admin user
            \App\Models\User::create([
                'name' => 'Demo Baker',
                'email' => 'demo@getkneadit.app',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Seed default settings
            \App\Models\Setting::set('store_name', 'Sweet Dreams Bakery');
            \App\Models\Setting::set('store_email', 'demo@getkneadit.app');
            \App\Models\Setting::set('store_phone', '(863) 555-0123');
            \App\Models\Setting::set('store_address', '123 Main Street, Davenport, FL 33837');
            \App\Models\Setting::set('default_daily_capacity', '15');

            $this->info('Seeding demo data...');

            // Run all seeders inside tenant context
            \Artisan::call('db:seed', ['--force' => true]);
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
