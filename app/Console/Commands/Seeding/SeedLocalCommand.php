<?php

namespace App\Console\Commands\Seeding;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\PlatformActivity;
use App\Models\Platform\SupportReply;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Faker\Factory as Faker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

#[Signature('kneadit:seed-local
    {--count=100 : Number of tenants to provision}
    {--fresh : Drop existing tenants first (DESTRUCTIVE)}')]
#[Description('Seed local dev with N varied tenants + Central activity (slow — minutes)')]
class SeedLocalCommand extends Command
{
    /**
     * Branded honey palette pairs for randomized tenant branding.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const array PALETTES = [
        ['#d4920c', '#1c1410'],
        ['#c4841d', '#2a1810'],
        ['#8b5e3c', '#1a1210'],
        ['#b8722d', '#221814'],
        ['#a0651e', '#1e1410'],
        ['#e8a045', '#231914'],
        ['#9c6326', '#1a0f08'],
    ];

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $faker = Faker::create();

        if ($this->option('fresh')) {
            if (! $this->confirm('This will DELETE every existing tenant. Continue?')) {
                return self::SUCCESS;
            }
            $this->info('Wiping existing tenants…');
            foreach (Tenant::all() as $tenant) {
                $tenant->delete();
            }
        }

        $this->info("Provisioning {$count} tenants — this can take a while.");
        $this->newLine();

        $created = [];
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $spec = $this->generateTenantSpec($faker, $i);

            // Skip if a tenant with this id somehow exists.
            if (Tenant::query()->find($spec['id'])) {
                $bar->advance();

                continue;
            }

            $result = Process::timeout(120)->run(sprintf(
                'php artisan tenant:create-one %s %s %s %s %s %s',
                escapeshellarg($spec['id']),
                escapeshellarg($spec['name']),
                escapeshellarg($spec['email']),
                escapeshellarg($spec['store_name']),
                escapeshellarg($spec['brand_primary']),
                escapeshellarg($spec['brand_secondary']),
            ));

            if (! $result->successful()) {
                $this->newLine();
                $this->error("Failed to create {$spec['id']}: " . trim($result->errorOutput() ?: $result->output()));
                $bar->advance();

                continue;
            }

            // Re-fetch the tenant we just created and varied-attribute-ize it.
            /** @var Tenant|null $tenant */
            $tenant = Tenant::query()->find($spec['id']);
            if ($tenant !== null) {
                $this->randomizeTenantAttributes($tenant, $faker);
                $created[] = $tenant->id;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Generating Central activity…');
        $this->seedCentralActivity($created, $faker);

        $this->newLine();
        $this->info("✅ {$count} tenants provisioned. Login: any tenant email + password 'password'");

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function generateTenantSpec(\Faker\Generator $faker, int $index): array
    {
        $storeName = ucfirst($faker->word) . ' ' . $faker->randomElement(['Bakery', 'Bakehouse', 'Sweets', 'Kitchen', 'Crust', 'Crumbs', 'Bakes', 'Patisserie']);
        $id = Str::slug($storeName) . '-' . ($index + 1);
        $palette = self::PALETTES[array_rand(self::PALETTES)];

        return [
            'id' => $id,
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'store_name' => $storeName,
            'brand_primary' => $palette[0],
            'brand_secondary' => $palette[1],
        ];
    }

    private function randomizeTenantAttributes(Tenant $tenant, \Faker\Generator $faker): void
    {
        $createdAt = $faker->dateTimeBetween('-6 months', 'now');

        $plan = $faker->randomElement([
            SubscriptionTier::Starter,
            SubscriptionTier::Starter,
            SubscriptionTier::Growth,
            SubscriptionTier::Growth,
            SubscriptionTier::Pro,
        ]);

        $isActive = $faker->boolean(85);
        $freeForever = $faker->boolean(5);

        $trialDays = config('kneadit.trial_days', 30);
        $trialEndsAt = $faker->boolean(60)
            ? \Illuminate\Support\Carbon::instance($createdAt)->addDays($trialDays)
            : null;

        $tenant->update([
            'plan' => $plan,
            'is_active' => $isActive,
            'free_forever' => $freeForever,
            'trial_ends_at' => $trialEndsAt,
            'created_at' => $createdAt,
            'storefront_enabled' => $faker->boolean(80),
        ]);

        if ($freeForever) {
            FreeForeverGrant::query()->create([
                'tenant_id' => $tenant->id,
                'granted_at' => $createdAt,
            ]);
        }

        // Activity row: tenant_created
        PlatformActivity::query()->create([
            'event' => 'tenant_created',
            'tenant_id' => $tenant->id,
            'description' => "{$tenant->store_name} signed up on the {$plan->value} plan.",
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * @param array<int, string> $tenantIds
     */
    private function seedCentralActivity(array $tenantIds, \Faker\Generator $faker): void
    {
        if ($tenantIds === []) {
            return;
        }

        // Audit log entries — random admin actions distributed over time
        $adminUser = User::query()->orderBy('id')->first();
        $adminName = $adminUser instanceof User ? $adminUser->name : 'Admin';
        $actions = ['updated_tenant', 'changed_plan', 'extended_trial', 'impersonated', 'sent_announcement'];

        $auditTargets = collect($tenantIds)->random(min(40, count($tenantIds)));
        foreach ($auditTargets as $tenantId) {
            AdminAuditLog::query()->create([
                'admin_id' => $adminUser?->id,
                'action' => $faker->randomElement($actions),
                'description' => $faker->sentence,
                'target_type' => 'tenant',
                'target_id' => $tenantId,
                'user_name' => $adminName,
                'ip_address' => $faker->ipv4,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }

        // Support tickets — sample across tenants with mix of statuses
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['low', 'normal', 'high'];
        $sampleTenants = collect($tenantIds)->random(min(20, count($tenantIds)));

        foreach ($sampleTenants as $tenantId) {
            $createdAt = $faker->dateTimeBetween('-2 months', 'now');
            $status = $faker->randomElement($statuses);

            $ticket = SupportTicket::query()->create([
                'tenant_id' => $tenantId,
                'subject' => $faker->sentence(4),
                'body' => $faker->paragraph(2),
                'status' => $status,
                'priority' => $faker->randomElement($priorities),
                'resolved_at' => in_array($status, ['resolved', 'closed'], true)
                    ? $faker->dateTimeBetween($createdAt, 'now')
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // 50% chance of an admin reply
            if ($faker->boolean) {
                SupportReply::query()->create([
                    'ticket_id' => $ticket->id,
                    'author_type' => 'admin',
                    'author_name' => $adminName,
                    'body' => $faker->paragraph,
                    'created_at' => $faker->dateTimeBetween($createdAt, 'now'),
                    'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
                ]);
            }
        }
    }
}
