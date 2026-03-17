<?php

use App\Models\SupportTicket;
use App\Models\Tenant;
use Tests\CentralTestCase;

uses(CentralTestCase::class);

test('mrr calculation with different plans', function () {
    $this->createTenant(['id' => 'bakery1', 'name' => 'B1', 'email' => 'b1@test.com', 'plan' => 'starter', 'is_active' => true]);
    $this->createTenant(['id' => 'bakery2', 'name' => 'B2', 'email' => 'b2@test.com', 'plan' => 'growth', 'is_active' => true]);
    $this->createTenant(['id' => 'bakery3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => 'pro', 'is_active' => true]);
    $this->createTenant(['id' => 'bakery4', 'name' => 'B4', 'email' => 'b4@test.com', 'plan' => 'starter', 'is_active' => false]);

    $planPrices = ['starter' => 9, 'growth' => 19, 'pro' => 29];
    $activeTenants = Tenant::where('is_active', true)->get();
    $mrr = $activeTenants->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

    expect($mrr)->toBe(57);
    expect($activeTenants)->toHaveCount(3);
});

test('trial count', function () {
    $this->createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(7)]);
    $this->createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);
    $this->createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->subDays(1)]);
    $this->createTenant(['id' => 't4', 'name' => 'T4', 'email' => 't4@test.com', 'plan' => 'growth']);

    $trialCount = Tenant::whereNotNull('trial_ends_at')
        ->where('trial_ends_at', '>', now())
        ->count();

    expect($trialCount)->toBe(2);
});

test('open tickets count', function () {
    SupportTicket::create(['subject' => 'Help', 'body' => 'Need help', 'status' => 'open']);
    SupportTicket::create(['subject' => 'Bug', 'body' => 'Found bug', 'status' => 'open']);
    SupportTicket::create(['subject' => 'Done', 'body' => 'Resolved', 'status' => 'closed']);

    expect(SupportTicket::where('status', 'open')->count())->toBe(2);
});

test('total tenants count', function () {
    $this->createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => 'starter']);
    $this->createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => 'growth', 'is_active' => false]);

    expect(Tenant::count())->toBe(2);
});

test('mrr excludes inactive tenants', function () {
    $this->createTenant(['id' => 'i1', 'name' => 'I1', 'email' => 'i1@test.com', 'plan' => 'pro', 'is_active' => false]);

    $planPrices = ['starter' => 9, 'growth' => 19, 'pro' => 29];
    $mrr = Tenant::where('is_active', true)->get()->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

    expect($mrr)->toBe(0);
});
