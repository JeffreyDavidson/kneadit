<?php

use App\Actions\Staff\RemoveStaffMember;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Orders\Order;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('removes a staff member', function () {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->staff()->create();

    resolve(RemoveStaffMember::class)($staff->id, $owner->id);

    expect(User::query()->find($staff->id))->toBeNull();
});

test('preserves historical records and nulls the removed staff actor', function () {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->staff()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['user_id' => $staff->id]);
    $note = CustomerNote::factory()->for($customer)->create(['created_by' => $staff->id]);
    $invitation = StaffInvitation::factory()->create(['invited_by' => $staff->id]);

    resolve(RemoveStaffMember::class)($staff->id, $owner->id);

    expect(User::query()->find($staff->id))->toBeNull()
        ->and(Order::query()->find($order->id))->not->toBeNull()
        ->and(Order::query()->find($order->id)->user_id)->toBeNull()
        ->and(CustomerNote::query()->find($note->id))->not->toBeNull()
        ->and(CustomerNote::query()->find($note->id)->created_by)->toBeNull()
        ->and(StaffInvitation::query()->find($invitation->id))->not->toBeNull()
        ->and(StaffInvitation::query()->find($invitation->id)->invited_by)->toBeNull();
});

test('prevents removing yourself', function () {
    $owner = User::factory()->owner()->create();

    expect(fn () => resolve(RemoveStaffMember::class)($owner->id, $owner->id))
        ->toThrow(RuntimeException::class, "You can't remove yourself.");
});

test('prevents removing the last owner', function () {
    $owner = User::factory()->owner()->create();
    $manager = User::factory()->manager()->create();

    expect(fn () => resolve(RemoveStaffMember::class)($owner->id, $manager->id))
        ->toThrow(RuntimeException::class, "Can't remove the last owner.");
});

test('two owners removing each other at the same moment leaves one owner', function () {
    $first = User::factory()->owner()->create();
    $second = User::factory()->owner()->create();

    // SQLite ignores row locks and one process cannot race itself, so let the
    // other owner's removal commit right after this request's first owner count.
    $interleaved = false;
    DB::listen(function (QueryExecuted $query) use (&$interleaved, $first, $second): void {
        if ($interleaved || ! str_contains($query->sql, 'count(*)')) {
            return;
        }

        $interleaved = true;
        resolve(RemoveStaffMember::class)($second->id, $first->id);
    });

    expect(fn () => resolve(RemoveStaffMember::class)($first->id, $second->id))
        ->toThrow(RuntimeException::class, "Can't remove the last owner.")
        ->and(User::query()->owners()->pluck('id')->all())->toBe([$first->id]);
});
