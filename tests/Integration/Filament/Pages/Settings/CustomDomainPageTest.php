<?php

use App\Filament\Pages\Settings\CustomDomain;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new CustomDomain;
});

test('custom domain defaults to empty string', function () {
    expect(testFixture('page', CustomDomain::class)->custom_domain)->toBeEmpty();
});

test('dns status defaults to null', function () {
    expect(testFixture('page', CustomDomain::class)->dns_status)->toBeNull();
});

test('ssl status defaults to null', function () {
    expect(testFixture('page', CustomDomain::class)->ssl_status)->toBeNull();
});

test('check dns sets null when no domain', function () {
    testFixture('page', CustomDomain::class)->custom_domain = '';

    $method = new ReflectionMethod(CustomDomain::class, 'refreshDnsStatus');
    $method->invoke(testFixture('page', CustomDomain::class));

    expect(testFixture('page', CustomDomain::class)->dns_status)->toBeNull();
});

test('check dns sets pending for unknown domain', function () {
    testFixture('page', CustomDomain::class)->custom_domain = 'nonexistent-test-domain-12345.com';

    $method = new ReflectionMethod(CustomDomain::class, 'refreshDnsStatus');
    $method->invoke(testFixture('page', CustomDomain::class));

    expect(testFixture('page', CustomDomain::class)->dns_status)->toBe(App\Enums\Platform\DnsVerificationStatus::Pending);
});

test('provision ssl sets manual status when forge not configured', function () {
    $method = new ReflectionMethod(CustomDomain::class, 'handleSslProvisioning');
    $method->invoke(testFixture('page', CustomDomain::class), 'test.com');

    expect(testFixture('page', CustomDomain::class)->ssl_status)->toBe('manual');
});
