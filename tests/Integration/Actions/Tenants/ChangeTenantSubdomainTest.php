<?php

use App\Actions\Tenants\ChangeTenantSubdomain;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    setUpCentralTest();

    test()->tenant = createTenantWithDomain('bakery-on-biscotto', 'bakery-on-biscotto', ['subdomain' => 'bakery-on-biscotto']);
});

test('sets the new subdomain, adds a dot-less domain row and keeps the old one', function () {
    $subdomain = resolve(ChangeTenantSubdomain::class)(test()->tenant, 'bakeryonbiscotto');

    expect($subdomain)->toBe('bakeryonbiscotto')
        ->and(test()->tenant->refresh()->subdomain)->toBe('bakeryonbiscotto')
        ->and(test()->tenant->id)->toBe('bakery-on-biscotto')
        ->and(DB::table('domains')->where('tenant_id', 'bakery-on-biscotto')->orderBy('domain')->pluck('domain')->all())
        ->toBe(['bakery-on-biscotto', 'bakeryonbiscotto']);
});

test('normalizes the subdomain to lower case', function () {
    resolve(ChangeTenantSubdomain::class)(test()->tenant, ' BakeryOnBiscotto ');

    expect(test()->tenant->refresh()->subdomain)->toBe('bakeryonbiscotto');
});

test('running the same change twice is a no-op', function () {
    resolve(ChangeTenantSubdomain::class)(test()->tenant, 'bakeryonbiscotto');
    resolve(ChangeTenantSubdomain::class)(test()->tenant->refresh(), 'bakeryonbiscotto');

    expect(DB::table('domains')->where('domain', 'bakeryonbiscotto')->count())->toBe(1)
        ->and(test()->tenant->refresh()->subdomain)->toBe('bakeryonbiscotto');
});

test('can switch back to a subdomain the bakery used before', function () {
    resolve(ChangeTenantSubdomain::class)(test()->tenant, 'bakeryonbiscotto');
    resolve(ChangeTenantSubdomain::class)(test()->tenant->refresh(), 'bakery-on-biscotto');

    expect(test()->tenant->refresh()->subdomain)->toBe('bakery-on-biscotto');
});

test('refuses an unusable subdomain and changes nothing', function (string $subdomain, string $message) {
    createTenantWithDomain('other-bakery', 'other-bakery', ['email' => 'other@example.com', 'subdomain' => 'otherbrand']);
    createTenantWithDomain('legacy-row', 'legacyrow', ['email' => 'legacy@example.com']);

    $act = fn () => resolve(ChangeTenantSubdomain::class)(test()->tenant, $subdomain);

    expect($act)->toThrow(ValidationException::class, $message)
        ->and(test()->tenant->refresh()->subdomain)->toBe('bakery-on-biscotto')
        ->and(DB::table('domains')->where('tenant_id', 'bakery-on-biscotto')->count())->toBe(1);
})->with([
    'reserved' => ['admin', 'selected subdomain is invalid'],
    'invalid characters' => ['Bad_Name', 'Use lowercase letters, numbers and hyphens'],
    'leading hyphen' => ['-bakery', 'Use lowercase letters, numbers and hyphens'],
    'another bakery id' => ['other-bakery', 'has already been taken'],
    'another bakery subdomain' => ['otherbrand', 'has already been taken'],
    'another bakery domain row' => ['legacyrow', 'has already been taken'],
]);

test('works for a bakery without a stored subdomain', function () {
    DB::table('tenants')->where('id', test()->tenant->id)->update(['subdomain' => null]);
    $tenant = Tenant::query()->findOrFail(test()->tenant->id);

    resolve(ChangeTenantSubdomain::class)($tenant, 'bakeryonbiscotto');

    expect($tenant->refresh()->subdomain)->toBe('bakeryonbiscotto');
});
