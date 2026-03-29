<?php

use App\Enums\SubscriptionTier;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

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
