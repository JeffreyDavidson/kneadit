<?php

use App\Actions\Platform\VerifyCustomDomain;
use App\Enums\Platform\DomainCheck;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpCentralTest();
    config([
        'services.forge.server_ip' => '203.0.113.10',
        'app.url' => 'http://kneadit.test',
        'tenancy.tenant_domain' => 'kneadit.test',
    ]);
    Date::setTestNow('2026-10-01 09:30');
});

test('DNS ok but no HTTPS leaves the domain unverified and clears an existing verification', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()->subDay()]);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::HttpsUnavailable)
        ->and($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('DNS ok and HTTPS ok records the verification time', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::Verified)
        ->and($tenant->refresh()->custom_domain_verified_at->toDateTimeString())->toBe('2026-10-01 09:30:00');
});

test('an already verified domain keeps its original verification time', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    $verifiedAt = now()->subDays(3);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => $verifiedAt]);

    resolve(VerifyCustomDomain::class)($tenant);

    expect($tenant->refresh()->custom_domain_verified_at->equalTo($verifiedAt))->toBeTrue();
});

test('a proxied domain that answers with the proof is verified', function () {
    fakeDnsRecords(['shop.example.com' => '104.21.0.1']);
    fakeHttpsProbe(['shop.example.com' => true]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::VerifiedThroughProxy)
        ->and($tenant->refresh()->custom_domain_verified_at->toDateTimeString())->toBe('2026-10-01 09:30:00');
});

test('a proxied domain that does not answer with the proof stays unverified', function () {
    fakeDnsRecords(['shop.example.com' => '104.21.0.1']);
    fakeHttpsProbe(['shop.example.com' => false]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()->subDay()]);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::DnsMissing)
        ->and($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('missing DNS clears the verification', function () {
    fakeDnsRecords([]);
    fakeHttpsProbe([]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()->subDay()]);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::DnsMissing)
        ->and($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('a bakery without a custom domain is not verified', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => null, 'custom_domain_verified_at' => now()->subDay()]);

    $result = resolve(VerifyCustomDomain::class)($tenant);

    expect($result)->toBe(DomainCheck::DnsMissing)
        ->and($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('links fall back to the subdomain when DNS is ok but HTTPS is failing', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    $tenant = Tenant::factory()->create(['id' => 'sunrise', 'custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()->subDay()]);
    $tenant->createDomain(['domain' => 'shop.example.com']);

    resolve(VerifyCustomDomain::class)($tenant);

    expect(resolve(TenantUrlGenerator::class)->primaryStorefront($tenant->refresh()))->toBe('http://sunrise.kneadit.test');
});
