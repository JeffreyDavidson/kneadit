<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebugTenantAuth extends Command
{
    protected $signature = 'tenant:debug-auth {tenant}';
    protected $description = 'Debug: test auth attempt in tenant context';

    public function handle(): int
    {
        $tenant = Tenant::find($this->argument('tenant'));
        if (! $tenant) {
            $this->error('Tenant not found');
            return self::FAILURE;
        }

        $tenant->run(function () {
            $this->info('Default DB connection: ' . DB::getDefaultConnection());
            $this->info('User model connection: ' . (app(\App\Models\User::class)->getConnectionName() ?? 'null (default)'));
            
            // Check what DB the users table is in
            $users = \App\Models\User::all();
            $this->info('Users found via Eloquent: ' . $users->count());
            foreach ($users as $u) {
                $this->info("  - {$u->email} (connection: " . ($u->getConnectionName() ?? 'default') . ")");
            }

            // Try auth attempt
            $result = Auth::attempt(['email' => 'sarah@sweetdreamsbakery.com', 'password' => 'password']);
            $this->info('Auth::attempt result: ' . ($result ? 'SUCCESS' : 'FAIL'));
        });

        return self::SUCCESS;
    }
}
