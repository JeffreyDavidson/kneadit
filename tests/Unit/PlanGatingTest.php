<?php

namespace Tests\Unit;

use App\Traits\HasPlanGating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Contracts\Tenant;
use Tests\TestCase;

class PlanGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    public function test_has_plan_gating_trait_exists(): void
    {
        $this->assertTrue(
            trait_exists(\App\Traits\HasPlanGating::class),
            'HasPlanGating trait should exist at app/Traits/HasPlanGating.php'
        );
    }

    public function test_starter_plan_can_access_starter_features(): void
    {
        $this->setTenantPlan('starter');
        $this->assertTrue(StarterGatedResource::canAccess());
    }

    public function test_starter_plan_cannot_access_growth_features(): void
    {
        $this->setTenantPlan('starter');
        $this->assertFalse(GrowthGatedResource::canAccess());
    }

    public function test_starter_plan_cannot_access_pro_features(): void
    {
        $this->setTenantPlan('starter');
        $this->assertFalse(ProGatedResource::canAccess());
    }

    public function test_growth_plan_can_access_starter_features(): void
    {
        $this->setTenantPlan('growth');
        $this->assertTrue(StarterGatedResource::canAccess());
    }

    public function test_growth_plan_can_access_growth_features(): void
    {
        $this->setTenantPlan('growth');
        $this->assertTrue(GrowthGatedResource::canAccess());
    }

    public function test_growth_plan_cannot_access_pro_features(): void
    {
        $this->setTenantPlan('growth');
        $this->assertFalse(ProGatedResource::canAccess());
    }

    public function test_pro_plan_can_access_all_features(): void
    {
        $this->setTenantPlan('pro');
        $this->assertTrue(StarterGatedResource::canAccess());
        $this->assertTrue(GrowthGatedResource::canAccess());
        $this->assertTrue(ProGatedResource::canAccess());
    }

    public function test_navigation_badge_shows_plan_name_for_locked_features(): void
    {
        $this->setTenantPlan('starter');
        $this->assertEquals('GROWTH', GrowthGatedResource::getNavigationBadge());
        $this->assertEquals('PRO', ProGatedResource::getNavigationBadge());
    }

    public function test_navigation_badge_is_null_for_accessible_features(): void
    {
        $this->setTenantPlan('growth');
        $this->assertNull(StarterGatedResource::getNavigationBadge());
        $this->assertNull(GrowthGatedResource::getNavigationBadge());
    }

    public function test_navigation_badge_color_is_info_for_growth(): void
    {
        $this->setTenantPlan('starter');
        $this->assertEquals('info', GrowthGatedResource::getNavigationBadgeColor());
    }

    public function test_navigation_badge_color_is_warning_for_pro(): void
    {
        $this->setTenantPlan('starter');
        $this->assertEquals('warning', ProGatedResource::getNavigationBadgeColor());
    }

    public function test_navigation_badge_color_is_null_when_accessible(): void
    {
        $this->setTenantPlan('pro');
        $this->assertNull(ProGatedResource::getNavigationBadgeColor());
    }

    private function setTenantPlan(string $plan): void
    {
        $tenant = new class($plan) implements Tenant
        {
            public string $plan;

            public function __construct(string $plan)
            {
                $this->plan = $plan;
            }

            public function getTenantKeyName(): string
            {
                return 'id';
            }

            public function getTenantKey()
            {
                return 'test';
            }

            public function getInternal(string $key)
            {
                return $key === 'plan' ? $this->plan : null;
            }

            public function setInternal(string $key, $value) {}

            public function run(callable $callback)
            {
                return $callback($this);
            }
        };

        app()->instance(Tenant::class, $tenant);
    }
}

class StarterGatedResource
{
    use HasPlanGating;

    protected static string $requiredPlan = 'starter';
}

class GrowthGatedResource
{
    use HasPlanGating;

    protected static string $requiredPlan = 'growth';
}

class ProGatedResource
{
    use HasPlanGating;

    protected static string $requiredPlan = 'pro';
}
