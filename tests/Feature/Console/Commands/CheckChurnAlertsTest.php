<?php

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\AdminAuditLog;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    setUpCentralTest();
});

test('command runs without errors', function () {
    $this->artisan('churn:check')->assertSuccessful();
});

test('trial expiring in 48h creates churn alert', function () {
    DB::table('tenants')->insert([
        'id' => 'expiring-bakery',
        'name' => 'Expiring Bakery',
        'email' => 'expiring@example.com',
        'plan' => SubscriptionTier::Starter,
        'trial_ends_at' => Date::now()->addHours(24),
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'expiring-bakery')
        ->first();

    expect($log)->not->toBeNull('Expected a churn_alert audit log for expiring tenant')->and($log->description)->toContain('Trial expiring soon');
});

test('no login in 7+ days creates churn alert', function () {
    createTenant([
        'id' => 'no-login-bakery',
        'name' => 'No Login Baker',
        'email' => 'nologin@test.com',
        'store_name' => 'No Login Bakery',
    ]);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturnUsing(function ($tenant, $callback) {
            // Return a date 10 days ago for the last login check
            return now()->subDays(10)->toDateTimeString();
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'no-login-bakery')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('No login');
});

test('zero orders creates churn alert for old tenants', function () {
    $minAgeDays = config('monitoring.churn_min_tenant_age_days', 14);

    createTenant([
        'id' => 'no-orders-bakery',
        'name' => 'No Orders Baker',
        'email' => 'noorders@test.com',
        'store_name' => 'No Orders Bakery',
        'created_at' => now()->subDays($minAgeDays + 5),
        'updated_at' => now()->subDays($minAgeDays + 5),
    ]);

    $callCount = 0;
    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturnUsing(function ($tenant, $callback) use (&$callCount) {
            $callCount++;

            // First call is for last login — return recent login (no alert)
            // Second call is for order count — return 0
            if ($callCount % 2 === 1) {
                return now()->toDateTimeString();
            }

            return 0;
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'no-orders-bakery')
        ->whereJsonContains('metadata->type', 'no_orders')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Zero orders');
});

test('young tenants are not checked for zero orders', function () {
    createTenant([
        'id' => 'young-bakery',
        'name' => 'Young Baker',
        'email' => 'young@test.com',
        'store_name' => 'Young Bakery',
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ]);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturnUsing(function ($tenant, $callback) {
            // Return recent login so no login alert is triggered
            return now()->toDateTimeString();
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'young-bakery')
        ->whereJsonContains('metadata->type', 'no_orders')
        ->first();

    expect($log)->toBeNull();
});

test('tenant with trial far in the future does not trigger trial alert', function () {
    DB::table('tenants')->insert([
        'id' => 'healthy-bakery',
        'name' => 'Healthy Bakery',
        'email' => 'healthy@example.com',
        'plan' => SubscriptionTier::Starter,
        'trial_ends_at' => Date::now()->addDays(20),
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturn(now()->toDateTimeString());

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'healthy-bakery')
        ->whereJsonContains('metadata->type', 'trial_expiring')
        ->first();

    expect($log)->toBeNull();
});

test('setup score query failure is handled gracefully', function () {
    DB::table('tenants')->insert([
        'id' => 'score-fail-bakery',
        'name' => 'Score Fail Bakery',
        'email' => 'scorefail@example.com',
        'plan' => SubscriptionTier::Starter,
        'trial_ends_at' => Date::now()->addHours(12),
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $callCount = 0;
    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturnUsing(function ($tenant, $callback) use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                // Setup score call — throw
                throw new RuntimeException('Tenant DB not found');
            }

            // last login call
            return now()->toDateTimeString();
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('warning')
        ->atLeast()->once();

    $this->artisan('churn:check')->assertSuccessful();
});

test('command outputs total alert count', function () {
    $this->artisan('churn:check')
        ->expectsOutputToContain('Churn check complete. 0 alert(s) logged.')
        ->assertSuccessful();
});
