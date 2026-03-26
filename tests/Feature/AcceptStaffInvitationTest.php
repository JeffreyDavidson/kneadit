<?php

use App\Actions\AcceptStaffInvitation;
use App\Enums\UserRole;
use App\Models\StaffInvitation;
use App\Models\User;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

it('creates a new user and marks invitation as accepted', function () {
    $owner = User::query()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $invitation = StaffInvitation::query()->create([
        'email' => 'new@test.com',
        'role' => UserRole::Staff->value,
        'token' => 'test-token-123',
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    $action = new AcceptStaffInvitation;
    $user = $action(
        invitation: $invitation,
        name: 'New Staff',
        password: 'password123',
    );

    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('new@test.com');
    expect($user->role)->toBe(UserRole::Staff);
    expect($invitation->refresh()->accepted_at)->not->toBeNull();
});
