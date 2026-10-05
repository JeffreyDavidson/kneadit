<?php

use App\Models\Platform\Tenant;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();

    // Mirror production: the tenant domain is not listed as a central domain.
    config([
        'tenancy.central_domains' => ['app.getkneadit.app'],
        'tenancy.tenant_domain' => 'getkneadit.app',
    ]);
});

test('a bakery subdomain of the tenant domain identifies the tenant', function (string $path) {
    $tenant = Tenant::factory()->create(['id' => 'biscotto']);
    $tenant->domains()->create(['domain' => 'biscotto']);

    get("https://biscotto.getkneadit.app{$path}")
        ->assertOk();
})->with(['storefront' => '/', 'admin login' => '/admin/login']);

test('a verified custom domain still identifies the tenant', function () {
    $tenant = Tenant::factory()->create(['id' => 'custombakery']);
    $tenant->domains()->create(['domain' => 'shop.customexample.com']);

    get('https://shop.customexample.com/')
        ->assertOk();
});

test('hosts that match no bakery return 404', function (string $host) {
    get("https://{$host}/")
        ->assertNotFound();
})->with([
    'unknown subdomain' => 'nope.getkneadit.app',
    'unrelated host' => 'unrelated.example.org',
    'nested subdomain' => 'a.nope.getkneadit.app',
]);
