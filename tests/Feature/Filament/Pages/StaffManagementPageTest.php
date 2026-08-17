<?php

use App\Enums\Staff\UserRole;
use App\Filament\Pages\Operations\StaffManagement;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
    test()->actingAs(test()->user);
});

test('staff management page can render', function () {
    Livewire::test(StaffManagement::class)->assertOk();
});

test('invite action sends an invitation', function () {
    Livewire::test(StaffManagement::class)
        ->callAction('invite', data: [
            'email' => 'newhire@example.com',
            'role' => UserRole::Staff->value,
        ]);

    expect(StaffInvitation::query()->where('email', 'newhire@example.com')->exists())->toBeTrue();
});

test('invite action requires a valid email', function () {
    Livewire::test(StaffManagement::class)
        ->callAction('invite', data: [
            'email' => 'not-an-email',
            'role' => UserRole::Staff->value,
        ])
        ->assertHasFormErrors(['email']);
});

test('change role action updates the member role', function () {
    $member = User::factory()->staff()->create();

    Livewire::test(StaffManagement::class)
        ->callAction('changeRole', arguments: ['user' => $member->id], data: [
            'role' => UserRole::Manager->value,
        ]);

    expect($member->refresh()->role)->toBe(UserRole::Manager);
});

test('change role action refuses to change own role', function () {
    Livewire::test(StaffManagement::class)
        ->callAction('changeRole', arguments: ['user' => test()->user->id], data: [
            'role' => UserRole::Manager->value,
        ]);

    expect(test()->user->refresh()->role)->toBe(UserRole::Owner);
});

test('remove member action deletes the user', function () {
    $member = User::factory()->staff()->create();

    Livewire::test(StaffManagement::class)
        ->callAction('removeMember', arguments: ['user' => $member->id]);

    expect(User::query()->find($member->id))->toBeNull();
});

test('remove member action refuses to remove the last owner', function () {
    $owner = test()->user;

    Livewire::test(StaffManagement::class)
        ->callAction('removeMember', arguments: ['user' => $owner->id]);

    expect(User::query()->find($owner->id))->not->toBeNull();
});

test('revoke invitation action deletes the invitation', function () {
    $invitation = StaffInvitation::factory()->create([
        'accepted_at' => null,
        'expires_at' => now()->addDay(),
    ]);

    Livewire::test(StaffManagement::class)
        ->callAction('revokeInvitation', arguments: ['invitation' => $invitation->id]);

    expect(StaffInvitation::query()->find($invitation->id))->toBeNull();
});
