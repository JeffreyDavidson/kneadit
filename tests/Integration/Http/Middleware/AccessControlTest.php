<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Stancl\Tenancy\Contracts\Tenant;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function setTenantPlanForAccess(string $plan): void
{
    $tenant = new class($plan) implements Tenant {
        public ?SubscriptionTier $plan;

        public function __construct(string $plan)
        {
            $this->plan = SubscriptionTier::from($plan);
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

    Feature::purge(['growth-features', 'pro-features']);
}

test('growth-features is active for growth plan', function () {
    setTenantPlanForAccess('growth');

    expect(Feature::active('growth-features'))->toBeTrue();
});

test('growth-features is inactive for starter plan', function () {
    setTenantPlanForAccess('starter');

    expect(Feature::active('growth-features'))->toBeFalse();
});

test('pro-features is active for pro plan', function () {
    setTenantPlanForAccess('pro');

    expect(Feature::active('pro-features'))->toBeTrue();
});

test('pro-features is inactive for growth plan', function () {
    setTenantPlanForAccess('growth');

    expect(Feature::active('pro-features'))->toBeFalse();
});

test('pro plan has access to both growth and pro features', function () {
    setTenantPlanForAccess('pro');

    expect(Feature::active('growth-features'))->toBeTrue()->and(Feature::active('pro-features'))->toBeTrue();
});

test('starter plan has no access to growth or pro features', function () {
    setTenantPlanForAccess('starter');

    expect(Feature::active('growth-features'))->toBeFalse()->and(Feature::active('pro-features'))->toBeFalse();
});

// --- ShowsUpgradeBadge tests ---

test('navigation badge shows tier name for locked features', function () {
    setTenantPlanForAccess('starter');

    expect(GrowthBadgeStub::getNavigationBadge())->toBe('GROWTH')->and(ProBadgeStub::getNavigationBadge())->toBe('PRO');
});

test('navigation badge is null for accessible features', function () {
    setTenantPlanForAccess('growth');

    expect(GrowthBadgeStub::getNavigationBadge())->toBeNull();
});

test('navigation badge color is info for growth', function () {
    setTenantPlanForAccess('starter');

    expect(GrowthBadgeStub::getNavigationBadgeColor())->toBe('info');
});

test('navigation badge color is warning for pro', function () {
    setTenantPlanForAccess('starter');

    expect(ProBadgeStub::getNavigationBadgeColor())->toBe('warning');
});

test('navigation badge color is null when accessible', function () {
    setTenantPlanForAccess('pro');

    expect(ProBadgeStub::getNavigationBadgeColor())->toBeNull();
});

// --- Test stubs ---
// These inline classes trigger PSR-4 warnings but are intentional test fixtures.
// They must be in the global namespace to be called statically from Pest closures.

class GrowthBadgeStub
{
    use ShowsUpgradeBadge;

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }
}

class ProBadgeStub
{
    use ShowsUpgradeBadge;

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }
}
