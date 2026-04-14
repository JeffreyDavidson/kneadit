<?php

use App\Filament\Pages\Settings\CustomDomain;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new CustomDomain;
});

test('custom domain defaults to empty string', function () {
    expect(test()->page->custom_domain)->toBeEmpty();
});

test('dns status defaults to null', function () {
    expect(test()->page->dns_status)->toBeNull();
});

test('ssl status defaults to null', function () {
    expect(test()->page->ssl_status)->toBeNull();
});

test('check dns sets null when no domain', function () {
    test()->page->custom_domain = '';

    $method = new ReflectionMethod(CustomDomain::class, 'refreshDnsStatus');
    $method->invoke(test()->page);

    expect(test()->page->dns_status)->toBeNull();
});

test('check dns sets pending for unknown domain', function () {
    test()->page->custom_domain = 'nonexistent-test-domain-12345.com';

    $method = new ReflectionMethod(CustomDomain::class, 'refreshDnsStatus');
    $method->invoke(test()->page);

    expect(test()->page->dns_status)->toBe('pending');
});

test('provision ssl sets manual status when forge not configured', function () {
    $method = new ReflectionMethod(CustomDomain::class, 'handleSslProvisioning');
    $method->invoke(test()->page, 'test.com');

    expect(test()->page->ssl_status)->toBe('manual');
});
