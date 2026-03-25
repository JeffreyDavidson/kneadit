<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Mail\WeeklyDigest;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly';

    protected $description = 'Send weekly digest email to bakery owners';

    public function handle(): int
    {
        $tenants = Tenant::cursor();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            try {
                if (Setting::get('weekly_digest_enabled', '1') !== '1') {
                    $this->info("Skipping {$tenant->id} — digest disabled");

                    continue;
                }

                $users = User::query()->where('role', UserRole::Owner)->get();

                if ($users->isEmpty()) {
                    $users = User::query()->limit(1)->get();
                }

                foreach ($users as $user) {
                    Mail::to($user->email)->send(new WeeklyDigest);
                }

                $this->info("Sent digest for {$tenant->id} to {$users->count()} user(s)");
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
            }

            tenancy()->end();
        }

        return self::SUCCESS;
    }
}
