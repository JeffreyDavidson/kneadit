<?php

use App\Actions\Platform\RemoveCustomDomain;
use App\Models\Platform\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['services.forge.api_token' => null]);
});

test('clears custom domain from tenant', function () {
    $tenant = Tenant::factory()->createOne(['custom_domain' => 'old.example.com']);
    Domain::query()->create(['domain' => 'old.example.com', 'tenant_id' => $tenant->id]);

    resolve(RemoveCustomDomain::class)($tenant);

    expect($tenant->refresh()->custom_domain)->toBeNull();
});

test('deletes domain record', function () {
    $tenant = Tenant::factory()->createOne(['custom_domain' => 'old.example.com']);
    Domain::query()->create(['domain' => 'old.example.com', 'tenant_id' => $tenant->id]);

    resolve(RemoveCustomDomain::class)($tenant);

    expect(Domain::query()->where('domain', 'old.example.com')->exists())->toBeFalse();
});

test('handles tenant with no custom domain', function () {
    $tenant = Tenant::factory()->createOne(['custom_domain' => null]);

    resolve(RemoveCustomDomain::class)($tenant);

    expect($tenant->refresh()->custom_domain)->toBeNull();
});
