<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DebugTenantUser extends Command
{
    protected $signature = 'tenant:debug-user {tenant}';
    protected $description = 'Debug: check user password in tenant DB';

    public function handle(): int
    {
        $tenant = Tenant::find($this->argument('tenant'));
        if (! $tenant) {
            $this->error('Tenant not found');
            return self::FAILURE;
        }

        $tenant->run(function () {
            $users = DB::connection('tenant')->table('users')->get(['id', 'name', 'email', 'password']);
            foreach ($users as $user) {
                $this->info("User: {$user->email}");
                $this->info("  Hash: " . substr($user->password, 0, 20) . '...');
                $this->info("  Starts with \$2y\$: " . (str_starts_with($user->password, '$2y$') ? 'YES' : 'NO'));
                $this->info("  Hash length: " . strlen($user->password));
                $this->info("  password check: " . (Hash::check('password', $user->password) ? 'PASS' : 'FAIL'));
            }
        });

        return self::SUCCESS;
    }
}
