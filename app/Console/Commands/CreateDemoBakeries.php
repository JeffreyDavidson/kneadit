<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

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
            foreach ($bakeries as $b) {
                $existing = Tenant::find($b['id']);
                if ($existing) {
                    $this->info("Deleting {$b['store_name']}...");
                    $existing->delete();
                }
            }
        }

        foreach ($bakeries as $bakery) {
            if (Tenant::find($bakery['id'])) {
                $this->warn("{$bakery['store_name']} already exists. Use --fresh to recreate.");
                continue;
            }

            $this->info("Creating {$bakery['store_name']}...");

            // Run each tenant creation in a separate PHP process
            // This avoids Stancl's connection state leaking between tenants
            $result = Process::run(sprintf(
                'php artisan tenant:create-one %s %s %s %s %s %s',
                escapeshellarg($bakery['id']),
                escapeshellarg($bakery['name']),
                escapeshellarg($bakery['email']),
                escapeshellarg($bakery['store_name']),
                escapeshellarg($bakery['brand_primary']),
                escapeshellarg($bakery['brand_secondary']),
            ));

            if ($result->successful()) {
                $domain = $bakery['id'] . '.kneadit.test';
                $this->info("  ✅ {$bakery['store_name']} → http://{$domain}/admin");
            } else {
                $this->error("  ❌ Failed: " . trim($result->errorOutput() ?: $result->output()));
            }
        }

        $this->newLine();
        $this->info('All bakeries created! Login: password');
        $this->newLine();
        $this->warn('Add these to /etc/hosts:');
        foreach ($bakeries as $bakery) {
            $this->info("  127.0.0.1  {$bakery['id']}.kneadit.test");
        }

        return self::SUCCESS;
    }
}
