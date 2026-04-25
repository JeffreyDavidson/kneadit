<?php

namespace App\Console\Commands\Seeding;

use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\PlatformActivity;
use App\Models\Platform\SupportReply;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

#[Signature('kneadit:seed-demo {--fresh : Drop existing demo tenants first}')]
#[Description('Seed local with 5 polished demo bakeries + curated Central activity')]
class SeedDemoCommand extends Command
{
    /**
     * @var array<int, array<string, string>>
     */
    private const array BAKERIES = [
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

    public function handle(): int
    {
        if ($this->option('fresh')) {
            foreach (self::BAKERIES as $bakery) {
                $existing = Tenant::query()->find($bakery['id']);
                if ($existing) {
                    $this->info("Deleting {$bakery['store_name']}…");
                    $existing->delete();
                }
            }
        }

        $created = [];

        foreach (self::BAKERIES as $bakery) {
            if (Tenant::query()->find($bakery['id'])) {
                $this->warn("{$bakery['store_name']} already exists. Use --fresh to recreate.");

                continue;
            }

            $this->info("Creating {$bakery['store_name']}…");

            $result = Process::timeout(180)->run(sprintf(
                'php artisan tenant:create-one %s %s %s %s %s %s',
                escapeshellarg($bakery['id']),
                escapeshellarg($bakery['name']),
                escapeshellarg($bakery['email']),
                escapeshellarg($bakery['store_name']),
                escapeshellarg($bakery['brand_primary']),
                escapeshellarg($bakery['brand_secondary']),
            ));

            if (! $result->successful()) {
                $this->error('  ❌ Failed: ' . trim($result->errorOutput() ?: $result->output()));

                continue;
            }

            $domain = $bakery['id'] . '.kneadit.test';
            $this->info("  ✅ {$bakery['store_name']} → http://{$domain}/admin");
            $created[] = $bakery['id'];
        }

        $this->newLine();
        $this->info('Adding Central activity for the demo…');
        $this->seedCentralActivity($created);

        $this->newLine();
        $this->info('All bakeries created! Login: any owner email + password "password"');
        $this->newLine();
        $this->warn('Add these to /etc/hosts:');
        foreach (self::BAKERIES as $bakery) {
            $this->info("  127.0.0.1  {$bakery['id']}.kneadit.test");
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int, string> $createdIds
     */
    private function seedCentralActivity(array $createdIds): void
    {
        if ($createdIds === []) {
            return;
        }

        $admin = User::query()->orderBy('id')->first();
        $adminName = $admin instanceof User ? $admin->name : 'Admin';

        // PlatformActivity rows — one signup event per tenant, dated to vary
        $offsets = [120, 90, 60, 30, 7]; // days ago for each demo tenant
        foreach ($createdIds as $i => $tenantId) {
            $tenant = Tenant::query()->find($tenantId);
            if (! $tenant) {
                continue;
            }

            $signupDate = now()->subDays($offsets[$i] ?? 30);
            $tenant->update(['created_at' => $signupDate]);

            PlatformActivity::query()->create([
                'event' => 'tenant_created',
                'tenant_id' => $tenantId,
                'description' => "{$tenant->store_name} signed up.",
                'created_at' => $signupDate,
                'updated_at' => $signupDate,
            ]);
        }

        // One open and one resolved ticket — gives admins something to look at
        $samples = [
            [
                'tenant_id' => $createdIds[0] ?? null,
                'subject' => 'Stripe webhook isn\'t firing',
                'body' => 'Hi team — orders are completing fine but we\'re not getting the Stripe webhook hits we used to. Can you check?',
                'status' => 'open',
                'priority' => 'high',
                'created_at' => now()->subHours(4),
            ],
            [
                'tenant_id' => $createdIds[2] ?? null,
                'subject' => 'How do I add a holiday closure?',
                'body' => 'I want to mark July 4th as closed so customers can\'t order. Where in the admin do I do that?',
                'status' => 'resolved',
                'priority' => 'normal',
                'resolved_at' => now()->subDays(3),
                'created_at' => now()->subDays(5),
            ],
        ];

        foreach (array_filter($samples, fn ($s) => $s['tenant_id'] !== null) as $sample) {
            $ticket = SupportTicket::query()->create($sample + ['updated_at' => $sample['created_at']]);

            if ($sample['status'] === 'resolved') {
                SupportReply::query()->create([
                    'ticket_id' => $ticket->id,
                    'author_type' => 'admin',
                    'author_name' => $adminName,
                    'body' => 'Head to Settings → Schedule and add a holiday closure for July 4th. The storefront will show "closed" automatically.',
                    'created_at' => $sample['created_at']->copy()->addHour(),
                    'updated_at' => $sample['created_at']->copy()->addHour(),
                ]);
            }
        }

        // One audit log entry — recent admin action
        AdminAuditLog::query()->create([
            'admin_id' => $admin?->id,
            'action' => 'extended_trial',
            'description' => 'Extended trial by 30 days for ' . ($createdIds[1] ?? 'tenant'),
            'target_type' => 'tenant',
            'target_id' => $createdIds[1] ?? null,
            'user_name' => $adminName,
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
