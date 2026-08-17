<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Platform\Tenant as PlatformTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function setTenantPlanForAccess(string $plan): PlatformTenant
{
    $tenant = PlatformTenant::factory()->make([
        'plan' => SubscriptionTier::from($plan),
    ]);

    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);

    Feature::purge(['growth-features', 'pro-features']);

    return $tenant;
}

test('growth-features is active for growth plan', function () {
    $tenant = setTenantPlanForAccess('growth');

    expect(Feature::for($tenant)->active('growth-features'))->toBeTrue();
});

test('growth-features is inactive for starter plan', function () {
    $tenant = setTenantPlanForAccess('starter');

    expect(Feature::for($tenant)->active('growth-features'))->toBeFalse();
});

test('pro-features is active for pro plan', function () {
    $tenant = setTenantPlanForAccess('pro');

    expect(Feature::for($tenant)->active('pro-features'))->toBeTrue();
});

test('pro-features is inactive for growth plan', function () {
    $tenant = setTenantPlanForAccess('growth');

    expect(Feature::for($tenant)->active('pro-features'))->toBeFalse();
});

test('pro plan has access to both growth and pro features', function () {
    $tenant = setTenantPlanForAccess('pro');

    expect(Feature::for($tenant)->active('growth-features'))->toBeTrue()
        ->and(Feature::for($tenant)->active('pro-features'))->toBeTrue();
});

test('starter plan has no access to growth or pro features', function () {
    $tenant = setTenantPlanForAccess('starter');

    expect(Feature::for($tenant)->active('growth-features'))->toBeFalse()
        ->and(Feature::for($tenant)->active('pro-features'))->toBeFalse();
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
