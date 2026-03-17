<?php

use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('staff invitation model exists', function () {
    expect(class_exists(StaffInvitation::class))->toBeTrue();
});

test('invitation can be created', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    StaffInvitation::create([
        'email' => 'staff@example.com',
        'role' => 'staff',
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    $this->assertDatabaseHas('staff_invitations', ['email' => 'staff@example.com']);
});

test('invitation is expired when past expiry', function () {
    $invitation = StaffInvitation::create([
        'email' => 'staff@example.com',
        'role' => 'staff',
        'token' => Str::random(32),
        'expires_at' => now()->subDay(),
        'invited_by' => User::factory()->create(['role' => 'owner'])->id,
    ]);

    expect($invitation->isExpired())->toBeTrue();
});

test('invitation is pending when not accepted and not expired', function () {
    $invitation = StaffInvitation::create([
        'email' => 'staff@example.com',
        'role' => 'staff',
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => User::factory()->create(['role' => 'owner'])->id,
    ]);

    expect($invitation->isPending())->toBeTrue();
});

test('invitation is not pending when accepted', function () {
    $invitation = StaffInvitation::create([
        'email' => 'staff@example.com',
        'role' => 'staff',
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'accepted_at' => now(),
        'invited_by' => User::factory()->create(['role' => 'owner'])->id,
    ]);

    expect($invitation->isPending())->toBeFalse();
});

test('invitation belongs to inviter', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $invitation = StaffInvitation::create([
        'email' => 'staff@example.com',
        'role' => 'staff',
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    expect($invitation->inviter->id)->toBe($owner->id);
});
