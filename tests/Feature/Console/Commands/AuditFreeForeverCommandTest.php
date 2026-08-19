<?php

use App\Mail\Platform\UnapprovedFreeForeverAlertMail;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpCentralTest();
    Mail::fake();
});

test('does not alert when every free_forever tenant has an active grant', function () {
    $admin = User::factory()->platformAdmin()->create();
    $tenantId = 'approved-tenant';
    createTenant(['id' => $tenantId, 'free_forever' => true]);
    FreeForeverGrant::query()->create([
        'tenant_id' => $tenantId,
        'granted_by_user_id' => $admin->id,
        'granted_at' => now(),
    ]);

    pendingArtisan('platform:audit-free-forever')
        ->expectsOutputToContain('All free_forever tenants have an approved grant')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('alerts platform admins when a free_forever tenant has no grant', function () {
    $admin = User::factory()->platformAdmin()->create(['email' => 'platform@example.com']);
    createTenant(['id' => 'rogue-tenant', 'name' => 'Rogue Bakery', 'email' => 'rogue@example.com', 'free_forever' => true]);
    // No FreeForeverGrant row — this is the suspect row the audit should catch.

    pendingArtisan('platform:audit-free-forever')->assertSuccessful();

    Mail::assertQueued(UnapprovedFreeForeverAlertMail::class, function (UnapprovedFreeForeverAlertMail $mail) use ($admin): bool {
        $payload = $mail->unapproved;

        return $mail->hasTo($admin->email)
            && count($payload) === 1
            && $payload[0]['id'] === 'rogue-tenant';
    });
});

test('does not alert when the grant is revoked (tenant no longer free)', function () {
    $admin = User::factory()->platformAdmin()->create();
    createTenant(['id' => 'revoked-tenant', 'free_forever' => false]);
    FreeForeverGrant::query()->create([
        'tenant_id' => 'revoked-tenant',
        'granted_by_user_id' => $admin->id,
        'granted_at' => now()->subDay(),
        'revoked_at' => now(),
    ]);

    pendingArtisan('platform:audit-free-forever')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('alerts when the only grant for a free_forever tenant is revoked', function () {
    $admin = User::factory()->platformAdmin()->create(['email' => 'platform@example.com']);
    createTenant(['id' => 'stale-grant-tenant', 'free_forever' => true]);
    FreeForeverGrant::query()->create([
        'tenant_id' => 'stale-grant-tenant',
        'granted_by_user_id' => $admin->id,
        'granted_at' => now()->subDays(10),
        'revoked_at' => now()->subDay(),
    ]);

    pendingArtisan('platform:audit-free-forever')->assertSuccessful();

    Mail::assertQueued(UnapprovedFreeForeverAlertMail::class);
});

test('succeeds silently when no platform admins exist to alert', function () {
    // No User::factory()->platformAdmin() created.
    createTenant(['id' => 'orphaned-alert', 'free_forever' => true]);
    // No grant — would alert if there were admins.

    pendingArtisan('platform:audit-free-forever')
        ->expectsOutputToContain('No platform admins found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});
