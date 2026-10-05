<?php

use App\Models\Customers\Customer;
use App\Models\Operations\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    runCommandsAsOneTenant();
});

test('redacts secrets in existing activity log rows and keeps the rest', function () {
    $log = ActivityLog::factory()->create([
        'model_type' => Customer::class,
        'properties' => ['changes' => [
            'password' => '$2y$12$abcdefghijklmnopqrstuv',
            'remember_token' => 'plain-remember-token',
            'name' => 'Ada Lovelace',
        ]],
    ]);

    artisan('activity-log:redact-secrets')->assertSuccessful();

    expect($log->fresh()->properties)->toBe(['changes' => [
        'password' => '[redacted]',
        'remember_token' => '[redacted]',
        'name' => 'Ada Lovelace',
    ]]);
});

test('leaves rows without secrets untouched', function () {
    $log = ActivityLog::factory()->create(['properties' => ['changes' => ['status' => 'ready']]]);
    ActivityLog::factory()->create(['properties' => null]);

    artisan('activity-log:redact-secrets')
        ->expectsOutputToContain('Redacted 0 row(s)')
        ->assertSuccessful();

    expect($log->fresh()->properties)->toBe(['changes' => ['status' => 'ready']]);
});

test('is idempotent', function () {
    ActivityLog::factory()->create(['properties' => ['changes' => ['password' => 'hash']]]);

    artisan('activity-log:redact-secrets')
        ->expectsOutputToContain('Redacted 1 row(s)')
        ->assertSuccessful();
    artisan('activity-log:redact-secrets')
        ->expectsOutputToContain('Redacted 0 row(s)')
        ->assertSuccessful();

    expect(ActivityLog::query()->sole()->properties)->toBe(['changes' => ['password' => '[redacted]']]);
});
