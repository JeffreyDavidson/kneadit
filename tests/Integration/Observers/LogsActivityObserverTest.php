<?php

use App\Enums\Operations\ActivityAction;
use App\Models\Customers\Customer;
use App\Models\Operations\ActivityLog;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('writes an activity log entry when an observed model is created', function () {
    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->where('action', ActivityAction::Created)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe(ActivityAction::Created)
        ->and($log->user_name)->toBe('System')
        ->and($log->description)->toBe("Customer #{$customer->id} was created");
});

test('does not write a spurious Updated row when a Customer is created (referral code is set pre-insert)', function () {
    $customer = Customer::factory()->create();

    $logs = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->get();

    expect($logs)->toHaveCount(1)
        ->and($logs->first()->action)->toBe(ActivityAction::Created);
});

test('writes an activity log entry when an observed model is updated, with changes payload', function () {
    $customer = Customer::factory()->create(['name' => 'Original']);
    ActivityLog::query()->delete();

    $customer->update(['name' => 'Updated']);

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->where('action', ActivityAction::Updated)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->properties)->toHaveKey('changes')
        ->and($log->properties['changes'])->toHaveKey('name');
});

test('writes an activity log entry when an observed model is deleted', function () {
    $customer = Customer::factory()->create();
    $customerId = $customer->id;
    ActivityLog::query()->delete();

    $customer->delete();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customerId)
        ->where('action', ActivityAction::Deleted)
        ->first();

    expect($log)->not->toBeNull();
});

test('records the authenticated user name when a user is logged in', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    actingAs($user);

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->where('action', ActivityAction::Created)
        ->first();

    expect($log->user_name)->toBe('Ada Lovelace')
        ->and($log->user_id)->toBe($user->id);
});

test('falls back to System when no user is authenticated (regression: null-safe)', function () {
    $logger = Log::spy();

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_name)->toBe('System')
        ->and($log->user_id)->toBeNull();

    $logger->shouldNotHaveReceived('warning');
});

test('reads actor from context when set explicitly (simulates queue propagation)', function () {
    $user = User::factory()->create(['name' => 'Background Worker']);
    ActorContext::set($user);

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->first();

    expect($log->user_name)->toBe('Background Worker')
        ->and($log->user_id)->toBe($user->id);

    ActorContext::clear();
});

test('does not recurse when writing ActivityLog rows', function () {
    Customer::factory()->create();

    $activityLogSelfEntries = ActivityLog::query()
        ->where('model_type', ActivityLog::class)
        ->count();

    expect($activityLogSelfEntries)->toBe(0);
});

test('records the password and remember token keys as redacted when a customer resets their password', function () {
    $customer = Customer::factory()->create();
    ActivityLog::query()->delete();

    $customer->forceFill(['password' => 'a-new-password', 'remember_token' => 'remember-me-token'])->save();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->sole();

    expect($log->properties['changes'])->toHaveKeys(['password', 'remember_token'])
        ->and($log->properties['changes']['password'])->toBe('[redacted]')
        ->and($log->properties['changes']['remember_token'])->toBe('[redacted]')
        ->and(json_encode($log->properties))->not->toContain($customer->fresh()->password)
        ->and(json_encode($log->properties))->not->toContain('remember-me-token');
});

test('a remember-me sign-in logs the remember token as redacted', function () {
    $customer = Customer::factory()->withPassword()->create();
    ActivityLog::query()->delete();

    auth('customer')->login($customer, remember: true);

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->sole();

    expect($log->properties['changes'])->toBe(['remember_token' => '[redacted]']);
});

test('still records ordinary changes in the clear', function () {
    $customer = Customer::factory()->create(['name' => 'Before']);
    ActivityLog::query()->delete();

    $customer->update(['name' => 'After']);

    $log = ActivityLog::query()->where('model_type', Customer::class)->sole();

    expect($log->properties['changes']['name'])->toBe('After');
});

test('an actor who is not a user of this bakery database is logged by name with no user id', function () {
    $centralOwner = User::factory()->make(['id' => 987654, 'name' => 'Central Owner']);
    ActorContext::set($centralOwner);
    Log::shouldReceive('warning')->never();

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->sole();

    expect($log->user_id)->toBeNull()
        ->and($log->user_name)->toBe('Central Owner');
});

test('an actor who is a user of this bakery database keeps their user id', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    ActorContext::set($user);

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()->where('model_id', $customer->id)->sole();

    expect($log->user_id)->toBe($user->id);
});
