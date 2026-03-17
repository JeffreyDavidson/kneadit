<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateOneTenant extends Command
{
    protected $signature = 'tenant:create-one
                            {id : Tenant subdomain/id}
                            {name : Owner name}
                            {email : Owner email}
                            {store_name : Bakery name}
                            {brand_primary : Primary brand color}
                            {brand_secondary : Secondary brand color}';

    protected $description = 'Create a single demo tenant (called by tenant:bakeries)';

    protected $hidden = true;

    public function handle(): int
    {
        $id = $this->argument('id');
        $domain = $id.'.kneadit.test';

        // Create tenant (triggers CreateDatabase + MigrateDatabase via events)
        $tenant = Tenant::create([
            'id' => $id,
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(30),
            'store_name' => $this->argument('store_name'),
            'brand_color_primary' => $this->argument('brand_primary'),
            'brand_color_secondary' => $this->argument('brand_secondary'),
            'is_active' => true,
        ]);

        $tenant->domains()->create(['domain' => $domain]);
        $tenant->domains()->create(['domain' => $id]);

        // Explicitly migrate
        Artisan::call('tenants:migrate', [
            '--tenants' => [$id],
            '--force' => true,
        ]);

        // Seed using $tenant->run() — works in a clean process
        $tenant->run(function () use ($tenant) {
            // Use DB::connection('tenant') to ensure we hit the tenant DB
            // and raw table insert to avoid User model's 'hashed' cast double-hashing
            DB::connection('tenant')->table('users')->insert([
                'name' => $tenant->name,
                'email' => $tenant->email,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Setting::set('store_name', $tenant->store_name);
            Setting::set('store_email', $tenant->email);

            Artisan::call('db:seed', ['--force' => true]);
        });

        return self::SUCCESS;
    }
}
