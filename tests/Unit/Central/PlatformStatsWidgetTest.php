<?php

namespace Tests\Unit\Central;

use App\Models\SupportTicket;
use App\Models\Tenant;
use Tests\CentralTestCase;

class PlatformStatsWidgetTest extends CentralTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_mrr_calculation_with_different_plans(): void
    {
        $this->createTenant(['id' => 'bakery1', 'name' => 'B1', 'email' => 'b1@test.com', 'plan' => 'starter', 'is_active' => true]);
        $this->createTenant(['id' => 'bakery2', 'name' => 'B2', 'email' => 'b2@test.com', 'plan' => 'growth', 'is_active' => true]);
        $this->createTenant(['id' => 'bakery3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => 'pro', 'is_active' => true]);
        $this->createTenant(['id' => 'bakery4', 'name' => 'B4', 'email' => 'b4@test.com', 'plan' => 'starter', 'is_active' => false]);

        $planPrices = ['starter' => 9, 'growth' => 19, 'pro' => 29];
        $activeTenants = Tenant::where('is_active', true)->get();
        $mrr = $activeTenants->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

        $this->assertEquals(57, $mrr);
        $this->assertCount(3, $activeTenants);
    }

    public function test_trial_count(): void
    {
        $this->createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(7)]);
        $this->createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);
        $this->createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->subDays(1)]);
        $this->createTenant(['id' => 't4', 'name' => 'T4', 'email' => 't4@test.com', 'plan' => 'growth']);

        $trialCount = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();

        $this->assertEquals(2, $trialCount);
    }

    public function test_open_tickets_count(): void
    {
        SupportTicket::create(['subject' => 'Help', 'body' => 'Need help', 'status' => 'open']);
        SupportTicket::create(['subject' => 'Bug', 'body' => 'Found bug', 'status' => 'open']);
        SupportTicket::create(['subject' => 'Done', 'body' => 'Resolved', 'status' => 'closed']);

        $this->assertEquals(2, SupportTicket::where('status', 'open')->count());
    }

    public function test_total_tenants_count(): void
    {
        $this->createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => 'growth', 'is_active' => false]);

        $this->assertEquals(2, Tenant::count());
    }

    public function test_mrr_excludes_inactive_tenants(): void
    {
        $this->createTenant(['id' => 'i1', 'name' => 'I1', 'email' => 'i1@test.com', 'plan' => 'pro', 'is_active' => false]);

        $planPrices = ['starter' => 9, 'growth' => 19, 'pro' => 29];
        $mrr = Tenant::where('is_active', true)->get()->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

        $this->assertEquals(0, $mrr);
    }
}
