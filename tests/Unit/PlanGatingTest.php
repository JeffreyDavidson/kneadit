<?php

use App\Traits\HasPlanGating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Contracts\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

function setTenantPlan(string $plan): void
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

test('has plan gating trait exists', function () {
    expect(trait_exists(HasPlanGating::class))->toBeTrue('HasPlanGating trait should exist at app/Traits/HasPlanGating.php');
});

test('starter plan can access starter features', function () {
    setTenantPlan('starter');

    expect(StarterGatedResource::canAccess())->toBeTrue();
});

test('starter plan cannot access growth features', function () {
    setTenantPlan('starter');

    expect(GrowthGatedResource::canAccess())->toBeFalse();
});

test('starter plan cannot access pro features', function () {
    setTenantPlan('starter');

    expect(ProGatedResource::canAccess())->toBeFalse();
});

test('growth plan can access starter features', function () {
    setTenantPlan('growth');

    expect(StarterGatedResource::canAccess())->toBeTrue();
});

test('growth plan can access growth features', function () {
    setTenantPlan('growth');

    expect(GrowthGatedResource::canAccess())->toBeTrue();
});

test('growth plan cannot access pro features', function () {
    setTenantPlan('growth');

    expect(ProGatedResource::canAccess())->toBeFalse();
});

test('pro plan can access all features', function () {
    setTenantPlan('pro');

    expect(StarterGatedResource::canAccess())->toBeTrue();
    expect(GrowthGatedResource::canAccess())->toBeTrue();
    expect(ProGatedResource::canAccess())->toBeTrue();
});

test('navigation badge shows plan name for locked features', function () {
    setTenantPlan('starter');

    expect(GrowthGatedResource::getNavigationBadge())->toBe('GROWTH');
    expect(ProGatedResource::getNavigationBadge())->toBe('PRO');
});

test('navigation badge is null for accessible features', function () {
    setTenantPlan('growth');

    expect(StarterGatedResource::getNavigationBadge())->toBeNull();
    expect(GrowthGatedResource::getNavigationBadge())->toBeNull();
});

test('navigation badge color is info for growth', function () {
    setTenantPlan('starter');

    expect(GrowthGatedResource::getNavigationBadgeColor())->toBe('info');
});

test('navigation badge color is warning for pro', function () {
    setTenantPlan('starter');

    expect(ProGatedResource::getNavigationBadgeColor())->toBe('warning');
});

test('navigation badge color is null when accessible', function () {
    setTenantPlan('pro');

    expect(ProGatedResource::getNavigationBadgeColor())->toBeNull();
});

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
