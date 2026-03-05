<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateDemoBakeries extends Command
{
    protected $signature = 'tenant:bakeries {--fresh : Drop and recreate all demo tenants}';
    protected $description = 'Create 5 demo bakery tenants for local testing';

    public function handle(): int
    {
        $bakeries = [
            [
                'id' => 'sweetdreams',
                'name' => 'Sarah Mitchell',
                'email' => 'sarah@sweetdreamsbakery.com',
                'store_name' => 'Sweet Dreams Bakery',
                'brand_primary' => '#d4920c',
                'brand_secondary' => '#1c1410',
            ],
            [
                'id' => 'honeycomb',
                'name' => 'Maria Rodriguez',
                'email' => 'maria@honeycombbakes.com',
                'store_name' => 'Honeycomb Bakes',
                'brand_primary' => '#c4841d',
                'brand_secondary' => '#2a1810',
            ],
            [
                'id' => 'flourpower',
                'name' => 'Emma Thompson',
                'email' => 'emma@flourpower.co',
                'store_name' => 'Flour Power Kitchen',
                'brand_primary' => '#8b5e3c',
                'brand_secondary' => '#1a1210',
            ],
            [
                'id' => 'sugarcrust',
                'name' => 'Jessica Park',
                'email' => 'jessica@sugarcrustco.com',
                'store_name' => 'Sugar & Crust Co.',
                'brand_primary' => '#b8722d',
                'brand_secondary' => '#221814',
            ],
            [
                'id' => 'butterbliss',
                'name' => 'Amanda Chen',
                'email' => 'amanda@butterbliss.com',
                'store_name' => 'Butter Bliss Bakehouse',
                'brand_primary' => '#a0651e',
                'brand_secondary' => '#1e1410',
            ],
        ];

        if ($this->option('fresh')) {
            // Clean up central users from previous runs
            foreach ($bakeries as $b) {
                \App\Models\User::where('email', $b['email'])->delete();
            }
        }

        foreach ($bakeries as $bakery) {
            $domain = $bakery['id'] . '.kneadit.test';

            if ($this->option('fresh')) {
                $existing = Tenant::find($bakery['id']);
                if ($existing) {
                    $this->info("Deleting {$bakery['store_name']}...");
                    $existing->delete();
                }
            }

            if (Tenant::find($bakery['id'])) {
                $this->warn("{$bakery['store_name']} already exists. Use --fresh to recreate.");
                continue;
            }

            $this->info("Creating {$bakery['store_name']}...");

            $tenant = Tenant::create([
                'id' => $bakery['id'],
                'name' => $bakery['name'],
                'email' => $bakery['email'],
                'plan' => 'pro',
                'trial_ends_at' => now()->addDays(30),
                'store_name' => $bakery['store_name'],
                'brand_color_primary' => $bakery['brand_primary'],
                'brand_color_secondary' => $bakery['brand_secondary'],
                'is_active' => true,
            ]);

            $tenant->domains()->create(['domain' => $domain]);
            $tenant->domains()->create(['domain' => $bakery['id']]);

            $dbPath = database_path('tenant' . $bakery['id']);
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }

            Artisan::call('tenants:migrate', [
                '--tenants' => [$bakery['id']],
                '--force' => true,
            ]);

            // Seed tenant data via tenants:seed (properly switches DB connection)
            Artisan::call('tenants:seed', [
                '--tenants' => [$bakery['id']],
                '--force' => true,
            ]);

            // Set tenant-specific settings
            $tenant->run(function () use ($bakery) {
                \App\Models\Setting::set('store_name', $bakery['store_name']);
                \App\Models\Setting::set('store_email', $bakery['email']);
            });

            $this->info("  ✅ {$bakery['store_name']} → http://{$domain}/admin");
        }

        $this->newLine();
        $this->info('All 5 bakeries created! Login: password');
        $this->newLine();
        $this->warn('Add these to /etc/hosts:');
        foreach ($bakeries as $bakery) {
            $this->info("  127.0.0.1  {$bakery['id']}.kneadit.test");
        }

        return self::SUCCESS;
    }
}
