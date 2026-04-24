<?php

namespace App\Console\Commands\Platform;

use App\Enums\Staff\UserRole;
use App\Mail\Platform\UnapprovedFreeForeverAlertMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('platform:audit-free-forever')]
#[Description('Alert platform admins when a tenant is marked free_forever without an approved grant row.')]
class AuditFreeForeverCommand extends Command
{
    public function handle(): int
    {
        // Tenants with free_forever=true AND no active (non-revoked) grant.
        // Those are the suspect rows — someone flipped the flag outside the
        // admin UI that writes the grant ledger.
        $unapproved = Tenant::query()
            ->where('free_forever', true)
            ->whereDoesntHave('freeForeverGrants', fn ($q) => $q->whereNull('revoked_at'))
            ->get()
            ->map(fn (Tenant $tenant): array => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
            ])
            ->values()
            ->all();

        if ($unapproved === []) {
            $this->info('All free_forever tenants have an approved grant. ✅');

            return self::SUCCESS;
        }

        $this->warn(sprintf('Found %d unapproved free_forever tenant(s):', count($unapproved)));
        foreach ($unapproved as $tenant) {
            $this->line("  - {$tenant['id']} ({$tenant['name']}, {$tenant['email']})");
        }

        $admins = User::query()->where('role', UserRole::PlatformAdmin)->pluck('email')->filter()->all();
        if ($admins === []) {
            $this->warn('No platform admins found — nothing to alert.');

            return self::SUCCESS;
        }

        Mail::to($admins)->queue(new UnapprovedFreeForeverAlertMail($unapproved));
        $this->info('Alert queued to ' . count($admins) . ' platform admin(s).');

        return self::SUCCESS;
    }
}
