<?php

use App\Actions\Platform\AddCustomDomain;
use App\Models\Platform\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['services.forge.api_token' => null]);
});

test('updates tenant custom domain', function () {
    $tenant = Tenant::factory()->create();

    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');

    expect($tenant->refresh()->custom_domain)->toBe('custom.example.com');
});

test('creates domain record for tenant', function () {
    $tenant = Tenant::factory()->create();

    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');

    expect(Domain::query()->where('domain', 'custom.example.com')->exists())->toBeTrue();
});

test('does not duplicate domain record', function () {
    $tenant = Tenant::factory()->create();

    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');
    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');

    expect(Domain::query()->where('domain', 'custom.example.com')->count())->toBe(1);
});
