<?php

use App\Enums\Operations\ActivityAction;
use App\Models\Customers\Customer;
use App\Models\Operations\ActivityLog;
use App\Models\Staff\User;
use App\Services\Audit\ActorContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('writes an activity log entry when an observed model is created', function () {
    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe(ActivityAction::Created)
        ->and($log->user_name)->toBe('System')
        ->and($log->description)->toBe("Customer #{$customer->id} was created");
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
    Log::spy();

    $customer = Customer::factory()->create();

    $log = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_name)->toBe('System')
        ->and($log->user_id)->toBeNull();

    Log::shouldNotHaveReceived('warning');
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
