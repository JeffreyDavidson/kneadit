<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function setTenantPlanForAccess(string $plan): void
{
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(new Tenant([
        'id' => 'access-control-test',
        'plan' => SubscriptionTier::from($plan),
    ]));

    Feature::purge(['growth-features', 'pro-features']);
    Cache::flush();
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
