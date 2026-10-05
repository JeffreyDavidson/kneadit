<?php

use App\Actions\Staff\ChangeStaffRole;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

test('demotes one of two owners', function () {
    $first = User::factory()->owner()->create();
    $second = User::factory()->owner()->create();

    resolve(ChangeStaffRole::class)($second->id, UserRole::Manager, $first->id);

    expect($second->fresh()->role)->toBe(UserRole::Manager)
        ->and(User::query()->owners()->pluck('id')->all())->toBe([$first->id]);
});

test('prevents demoting the last owner', function (UserRole $newRole) {
    $owner = User::factory()->owner()->create();
    $manager = User::factory()->manager()->create();

    expect(fn () => resolve(ChangeStaffRole::class)($owner->id, $newRole, $manager->id))
        ->toThrow(RuntimeException::class, "Can't demote the last owner.")
        ->and($owner->fresh()->role)->toBe(UserRole::Owner);
})->with([
    'manager' => UserRole::Manager,
    'staff' => UserRole::Staff,
]);

test('keeping an owner as owner is allowed even when they are the last one', function () {
    $owner = User::factory()->owner()->create();
    $manager = User::factory()->manager()->create();

    resolve(ChangeStaffRole::class)($owner->id, UserRole::Owner, $manager->id);

    expect($owner->fresh()->role)->toBe(UserRole::Owner);
});

test('two owners demoting each other at the same moment leaves one owner', function () {
    $first = User::factory()->owner()->create();
    $second = User::factory()->owner()->create();

    // SQLite ignores row locks and one process cannot race itself, so let the
    // other owner's demotion commit right after this request's first owner count.
    $interleaved = false;
    DB::listen(function (QueryExecuted $query) use (&$interleaved, $first, $second): void {
        if ($interleaved || ! str_contains($query->sql, 'count(*)')) {
            return;
        }

        $interleaved = true;
        resolve(ChangeStaffRole::class)($first->id, UserRole::Staff, $second->id);
    });

    expect(fn () => resolve(ChangeStaffRole::class)($second->id, UserRole::Staff, $first->id))
        ->toThrow(RuntimeException::class, "Can't demote the last owner.")
        ->and($interleaved)->toBeTrue()
        ->and(User::query()->owners()->pluck('id')->all())->toBe([$second->id]);
});
