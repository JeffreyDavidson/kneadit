<?php

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\AdminAuditLog;
use App\Services\Tenants\TenantHealthService;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use JMac\Testing\Double;

beforeEach(function () {
    setUpCentralTest();
});

function doubleCommandHealthService(array $healthData = [], int $recentOrders = 0): void
{
    $healthService = Double::for(TenantHealthService::class);
    $healthService->allows('getTenantHealthData')->returns(collect($healthData));
    $healthService->allows('getRecentOrderCount')->returns($recentOrders);
    app()->instance(TenantHealthService::class, $healthService);
}

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
    doubleCommandHealthService([
        ['id' => 'expiring-bakery', 'health_score' => 30, 'setup_score' => 10],
    ]);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'expiring-bakery')
        ->first();

    expect($log)->not->toBeNull('Expected a churn_alert audit log for expiring tenant')
        ->and($log->description)->toContain('Trial Expiring');
});

test('no login in 7+ days creates churn alert', function () {
    DB::table('tenants')->insert([
        'id' => 'no-login-bakery',
        'name' => 'No Login Baker',
        'email' => 'nologin@test.com',
        'store_name' => 'No Login Bakery',
        'last_login_at' => now()->subDays(10),
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    doubleCommandHealthService();

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'no-login-bakery')
        ->first();

    expect($log)->not->toBeNull();
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
    doubleCommandHealthService([
        ['id' => 'no-orders-bakery', 'health_score' => 80, 'setup_score' => 70],
    ]);

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
        'last_login_at' => now(),
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('churn:check')->assertSuccessful();

    $log = AdminAuditLog::query()->where('action', 'churn_alert')
        ->where('target_id', 'healthy-bakery')
        ->whereJsonContains('metadata->type', 'trial_expiring')
        ->first();

    expect($log)->toBeNull();
});

test('command outputs total alert count', function () {
    $this->artisan('churn:check')
        ->expectsOutputToContain('Churn check complete. 0 alert(s) logged.')
        ->assertSuccessful();
});
