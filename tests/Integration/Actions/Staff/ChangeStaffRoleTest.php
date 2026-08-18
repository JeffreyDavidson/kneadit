<?php

use App\Actions\Staff\ChangeStaffRole;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('changes staff member role', function () {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->staff()->create();

    resolve(ChangeStaffRole::class)($staff->id, UserRole::Manager, $owner->id);

    expect($staff->fresh()->role)->toBe(UserRole::Manager);
});

test('prevents changing own role', function () {
    $owner = User::factory()->owner()->create();

    expect(fn () => resolve(ChangeStaffRole::class)($owner->id, UserRole::Staff, $owner->id))
        ->toThrow(RuntimeException::class, "You can't change your own role.");
});
