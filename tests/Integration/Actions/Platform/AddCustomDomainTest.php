<?php

use App\Actions\Platform\AddCustomDomain;
use App\Models\Platform\Tenant;
use App\Services\Platform\Contracts\ForgeClient;
use Illuminate\Validation\ValidationException;
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

test('rejects an unusable domain without saving or aliasing it', function (string $domain) {
    $forge = Mockery::mock(ForgeClient::class);
    $forge->shouldNotReceive('addDomainAlias');
    app()->instance(ForgeClient::class, $forge);
    config([
        'services.forge.token' => 'token',
        'tenancy.tenant_domain' => 'kneadit.app',
    ]);
    $otherTenant = Tenant::factory()->create();
    $otherTenant->createDomain(['domain' => 'taken.example.com']);
    $tenant = Tenant::factory()->create();

    $act = fn () => resolve(AddCustomDomain::class)($tenant, $domain);

    expect($act)->toThrow(ValidationException::class)
        ->and($tenant->refresh()->custom_domain)->toBeNull()
        ->and(Domain::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
})->with([
    'another bakery\'s domain' => 'taken.example.com',
    'another bakery\'s domain, differently written' => 'HTTPS://Taken.Example.com/',
    'central domain' => 'kneadit.test',
    'platform subdomain' => 'foo.kneadit.app',
    'not a domain' => 'not a domain',
    'empty after normalizing' => 'https:///',
]);

test('normalizes the domain before saving it', function () {
    $tenant = Tenant::factory()->create();

    $saved = resolve(AddCustomDomain::class)($tenant, ' HTTPS://Shop.Example.com./menu?x=1 ');

    expect($saved)->toBe('shop.example.com')
        ->and($tenant->refresh()->custom_domain)->toBe('shop.example.com')
        ->and(Domain::query()->where('domain', 'shop.example.com')->where('tenant_id', $tenant->id)->count())->toBe(1);
});

test('re-adding the bakery\'s own domain is a no-op success', function () {
    $forge = Mockery::mock(ForgeClient::class);
    $forge->shouldReceive('addDomainAlias')->once()->with('shop.example.com')->andReturnTrue();
    app()->instance(ForgeClient::class, $forge);
    config(['services.forge.token' => 'token']);
    $tenant = Tenant::factory()->create();
    resolve(AddCustomDomain::class)($tenant, 'shop.example.com');

    $saved = resolve(AddCustomDomain::class)($tenant, 'Shop.Example.com');

    expect($saved)->toBe('shop.example.com')
        ->and($tenant->refresh()->custom_domain)->toBe('shop.example.com')
        ->and(Domain::query()->where('domain', 'shop.example.com')->count())->toBe(1);
});

test('replacing the custom domain removes the old domain record and alias', function () {
    $forge = Mockery::mock(ForgeClient::class);
    $forge->shouldReceive('addDomainAlias')->once()->with('old.example.com')->andReturnTrue();
    $forge->shouldReceive('removeDomainAlias')->once()->with('old.example.com')->andReturnTrue();
    $forge->shouldReceive('addDomainAlias')->once()->with('new.example.com')->andReturnTrue();
    app()->instance(ForgeClient::class, $forge);
    config(['services.forge.token' => 'token']);
    $tenant = Tenant::factory()->create();
    resolve(AddCustomDomain::class)($tenant, 'old.example.com');

    resolve(AddCustomDomain::class)($tenant, 'new.example.com');

    expect($tenant->refresh()->custom_domain)->toBe('new.example.com')
        ->and(Domain::query()->where('domain', 'old.example.com')->exists())->toBeFalse()
        ->and(Domain::query()->where('domain', 'new.example.com')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

test('saving a new domain leaves it unverified', function () {
    $tenant = Tenant::factory()->create();

    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');

    expect($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('replacing a verified domain clears the verification', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => 'old.example.com', 'custom_domain_verified_at' => now()]);
    $tenant->createDomain(['domain' => 'old.example.com']);

    resolve(AddCustomDomain::class)($tenant, 'new.example.com');

    expect($tenant->refresh()->custom_domain)->toBe('new.example.com')
        ->and($tenant->custom_domain_verified_at)->toBeNull();
});

test('saving the same verified domain again keeps the verification', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => 'custom.example.com', 'custom_domain_verified_at' => now()]);
    $tenant->createDomain(['domain' => 'custom.example.com']);

    resolve(AddCustomDomain::class)($tenant, 'custom.example.com');

    expect($tenant->refresh()->custom_domain_verified_at)->not->toBeNull();
});
