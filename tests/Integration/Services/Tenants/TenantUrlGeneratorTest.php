<?php

declare(strict_types=1);

use App\Actions\Platform\AddCustomDomain;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;

beforeEach(function () {
    setUpCentralTest();

    config(['app.url' => 'http://kneadit.test:8000', 'tenancy.tenant_domain' => 'kneadit.test']);

    test()->tenant = Tenant::factory()->create(['id' => 'sunrise']);
});

test('the primary storefront is the subdomain when the bakery has no custom domain', function () {
    expect(resolve(TenantUrlGenerator::class)->primaryStorefront(test()->tenant))
        ->toBe('http://sunrise.kneadit.test:8000');
});

test('the primary storefront is the custom domain without the platform port', function () {
    resolve(AddCustomDomain::class)(test()->tenant, 'sweetdreams.test');

    expect(resolve(TenantUrlGenerator::class)->primaryStorefront(test()->tenant->refresh()))
        ->toBe('http://sweetdreams.test');
});

test('a custom domain that routes to another bakery is ignored', function () {
    Tenant::factory()->create(['id' => 'other'])->createDomain(['domain' => 'taken.test']);
    test()->tenant->update(['custom_domain' => 'taken.test']);

    expect(resolve(TenantUrlGenerator::class)->primaryStorefront(test()->tenant->refresh()))
        ->toBe('http://sunrise.kneadit.test:8000');
});
