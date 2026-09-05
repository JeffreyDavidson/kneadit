<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Facades\Config;

test('generates a tenant storefront URL', function () {
    Config::set('app.url', 'https://getkneadit.app');

    $url = resolve(TenantUrlGenerator::class)->storefront(new Tenant(['id' => 'test-bakery']));

    expect($url)->toBe('https://test-bakery.getkneadit.app');
});

test('preserves the configured port', function () {
    Config::set('app.url', 'http://kneadit.test:8000');

    $url = resolve(TenantUrlGenerator::class)->storefront(new Tenant(['id' => 'test-bakery']));

    expect($url)->toBe('http://test-bakery.kneadit.test:8000');
});

test('generates a storefront host for display', function () {
    Config::set('app.url', 'http://kneadit.test:8000');

    $host = resolve(TenantUrlGenerator::class)->storefrontHost(new Tenant(['id' => 'test-bakery']));

    expect($host)->toBe('test-bakery.kneadit.test:8000');
});

test('generates a tenant admin URL without base URL state', function () {
    Config::set('app.url', 'http://kneadit.test:8000/base?source=test#section');

    $url = resolve(TenantUrlGenerator::class)->admin(new Tenant(['id' => 'test-bakery']));

    expect($url)->toBe('http://test-bakery.kneadit.test:8000/admin');
});

test('uses the named route for impersonation without base path query or fragment', function () {
    Config::set('app.url', 'https://getkneadit.app/base?source=test#section');

    $url = resolve(TenantUrlGenerator::class)->impersonation(
        new Tenant(['id' => 'test-bakery']),
        'secret-token',
    );

    expect($url)->toBe('https://test-bakery.getkneadit.app/impersonate/secret-token');
});

test('defaults protocol-relative application URLs to https', function () {
    Config::set('app.url', '//getkneadit.app');

    $url = resolve(TenantUrlGenerator::class)->storefront(new Tenant(['id' => 'test-bakery']));

    expect($url)->toBe('https://test-bakery.getkneadit.app');
});

test('rejects application URLs without a host', function () {
    Config::set('app.url', 'not-a-url');

    expect(fn () => resolve(TenantUrlGenerator::class)->storefront(new Tenant(['id' => 'test-bakery'])))
        ->toThrow(UnexpectedValueException::class, 'The application URL must contain a host.');
});
