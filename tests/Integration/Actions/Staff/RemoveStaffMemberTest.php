<?php

use App\Actions\Staff\RemoveStaffMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('removes a staff member', function () {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->staff()->create();

    app(RemoveStaffMember::class)($staff->id, $owner->id);

    expect(User::query()->find($staff->id))->toBeNull();
});

test('prevents removing yourself', function () {
    $owner = User::factory()->owner()->create();

    expect(fn () => app(RemoveStaffMember::class)($owner->id, $owner->id))
        ->toThrow(RuntimeException::class, "You can't remove yourself.");
});

test('prevents removing the last owner', function () {
    $owner = User::factory()->owner()->create();
    $manager = User::factory()->manager()->create();

    expect(fn () => app(RemoveStaffMember::class)($owner->id, $manager->id))
        ->toThrow(RuntimeException::class, "Can't remove the last owner.");
});
