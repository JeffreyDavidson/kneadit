<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
            $domain = $bakery['id'] . '.kneadit.test';

            if (Tenant::find($bakery['id'])) {
                $this->warn("{$bakery['store_name']} already exists. Use --fresh to recreate.");
                continue;
            }

            $this->info("Creating {$bakery['store_name']}...");

            // Tenant::create fires TenantCreated event which runs CreateDatabase + MigrateDatabase
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

            // Seed: use tenants:run to execute db:seed in tenant context
            // This is how Stancl recommends running commands in tenant context
            $this->seedTenant($tenant, $bakery);

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

    private function seedTenant(Tenant $tenant, array $bakery): void
    {
        // Manually configure a direct connection to the tenant's SQLite database
        $dbName = 'tenant' . $bakery['id'];
        $dbPath = database_path($dbName);

        if (! file_exists($dbPath)) {
            $this->error("  Tenant database not found: {$dbPath}");
            return;
        }

        // Register a temporary direct connection
        config(["database.connections.tenant_direct" => [
            'driver' => 'sqlite',
            'database' => $dbPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        DB::purge('tenant_direct');

        // Create admin user directly
        DB::connection('tenant_direct')->table('users')->insert([
            'name' => $bakery['name'],
            'email' => $bakery['email'],
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default user for seeders
        DB::connection('tenant_direct')->table('users')->insert([
            'name' => 'KneadIt Baker',
            'email' => 'baker@kneaditbakery.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set store identity
        DB::connection('tenant_direct')->table('settings')->insert([
            ['key' => 'store_name', 'value' => $bakery['store_name'], 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_email', 'value' => $bakery['email'], 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Now switch default connection to this tenant DB so seeders work
        $previousDefault = DB::getDefaultConnection();
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        // Run seeders (they use default connection via Eloquent)
        try {
            $seeder = new \Database\Seeders\DatabaseSeeder();
            $seeder->run();
            $this->info("  Seeded successfully");
        } catch (\Exception $e) {
            $this->error("  Seeding error: " . $e->getMessage());
        }

        // Restore default connection to central DB
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        DB::purge('sqlite');
        DB::setDefaultConnection($previousDefault);
        DB::purge('tenant_direct');
    }
}
