<?php

use App\Actions\Staff\SendStaffInvitation;
use App\Enums\UserRole;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

it('creates an invitation and sends the email', function () {
    Mail::fake();

    $inviter = User::query()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $action = new SendStaffInvitation;
    $invitation = $action(
        email: 'new@test.com',
        role: UserRole::Staff,
        invitedBy: $inviter->id,
    );

    expect($invitation)->toBeInstanceOf(StaffInvitation::class);
    expect($invitation->email)->toBe('new@test.com');
    expect($invitation->role)->toBe(UserRole::Staff->value);
    expect($invitation->token)->toHaveLength(64);

    Mail::assertQueued(StaffInvitationMail::class);
});
