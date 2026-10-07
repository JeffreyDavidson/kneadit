<?php

use App\Enums\Platform\DnsVerificationStatus;
use App\Filament\Pages\Settings\CustomDomain;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Platform\Contracts\ForgeClient;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());

    test()->tenant = Tenant::factory()->create(['plan' => 'pro']);
    app()->instance(TenantContract::class, test()->tenant);
});

test('saving a domain that belongs to another bakery shows a validation error and saves nothing', function () {
    Tenant::factory()->create()->createDomain(['domain' => 'taken.example.com']);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'taken.example.com')
        ->call('save')
        ->assertHasErrors(['custom_domain']);

    expect(test()->tenant->refresh()->custom_domain)->toBeNull();
});

test('a free-forever bakery on the Starter plan can save a custom domain', function () {
    test()->tenant->update(['plan' => 'starter', 'free_forever' => true]);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'comped.example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect(test()->tenant->refresh()->custom_domain)->toBe('comped.example.com');
});

test('a Starter bakery that is not free forever cannot save a custom domain', function () {
    test()->tenant->update(['plan' => 'starter', 'free_forever' => false]);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'paid-less.example.com')
        ->call('save')
        ->assertNotified('Custom domains are available on Growth and Pro plans');

    expect(test()->tenant->refresh()->custom_domain)->toBeNull();
});

test('saving a pasted URL stores the normalized hostname', function () {
    livewire(CustomDomain::class)
        ->set('custom_domain', 'HTTPS://Shop.Example.com/')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('custom_domain', 'shop.example.com');

    expect(test()->tenant->refresh()->custom_domain)->toBe('shop.example.com')
        ->and(Domain::query()->where('domain', 'shop.example.com')->exists())->toBeTrue();
});

test('verifying DNS marks the domain verified when it points at the server', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    Date::setTestNow('2026-10-01 09:30');
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->call('verifyDns')
        ->assertSet('dns_status', DnsVerificationStatus::Verified);

    expect(test()->tenant->refresh()->custom_domain_verified_at->toDateTimeString())->toBe('2026-10-01 09:30:00');
});

test('verifying DNS clears the verification when the domain no longer points at the server', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '198.51.100.7']);
    test()->tenant->update(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()]);

    livewire(CustomDomain::class)
        ->call('verifyDns')
        ->assertSet('dns_status', DnsVerificationStatus::Pending);

    expect(test()->tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('saving a domain whose DNS is already correct verifies it', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'shop.example.com')
        ->call('save');

    expect(test()->tenant->refresh()->custom_domain_verified_at)->not->toBeNull();
});

test('saving a domain whose DNS is not set up leaves it unverified', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords([]);
    fakeHttpsProbe([]);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'shop.example.com')
        ->call('save');

    expect(test()->tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('the page says links use the subdomain until the domain is verified', function () {
    config(['services.forge.server_ip' => '203.0.113.10', 'app.url' => 'http://kneadit.test', 'tenancy.tenant_domain' => 'kneadit.test']);
    fakeDnsRecords([]);
    fakeHttpsProbe([]);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('Not verified')
        ->assertSee(sprintf('http://%s.kneadit.test', test()->tenant->id));
});

test('the page shows when the domain was verified', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    Date::setTestNow('2026-10-01 09:30');
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('Verified on Oct 1, 2026');
});

test('the page shows the DNS and HTTPS parts and offers a certificate request when only HTTPS fails', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('DNS: OK')
        ->assertSee('HTTPS: no valid certificate yet')
        ->assertSee('Request SSL certificate')
        ->assertSee('Not verified');
});

test('the page shows DNS and HTTPS as OK and no certificate request once the domain is verified', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('DNS: OK')
        ->assertSee('HTTPS: OK')
        ->assertDontSee('Request SSL certificate');
});

test('the page shows a proxied domain as served through a proxy and verified', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '104.21.0.1']);
    fakeHttpsProbe(['shop.example.com' => true]);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('served through a proxy')
        ->assertSee('HTTPS: OK')
        ->assertDontSee('DNS: not pointing here')
        ->assertDontSee('Request SSL certificate');

    expect(test()->tenant->refresh()->custom_domain_verified_at)->not->toBeNull();
});

test('the page offers no certificate request while DNS is not pointing at the server', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords([]);
    fakeHttpsProbe([]);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->assertSee('DNS: not pointing here')
        ->assertDontSee('Request SSL certificate');
});

test('requesting an SSL certificate asks Forge for one', function () {
    config(['services.forge.server_ip' => '203.0.113.10', 'services.forge.token' => 't', 'services.forge.organization' => 'o', 'services.forge.server_id' => '1', 'services.forge.site_id' => '2']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    $forge = Mockery::mock(ForgeClient::class);
    $forge->expects('obtainSslCertificate')->with('shop.example.com')->andReturn(true);
    app()->instance(ForgeClient::class, $forge);
    test()->tenant->update(['custom_domain' => 'shop.example.com']);

    livewire(CustomDomain::class)
        ->call('requestSsl')
        ->assertNotified('SSL Certificate Requested')
        ->assertSet('ssl_status', 'provisioning');
});

test('verifying a domain whose DNS is ok but HTTPS fails tells the baker and leaves it unverified', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    test()->tenant->update(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()]);

    livewire(CustomDomain::class)
        ->call('verifyDns')
        ->assertNotified('DNS OK, no HTTPS certificate yet')
        ->assertSet('dns_status', DnsVerificationStatus::Verified);

    expect(test()->tenant->refresh()->custom_domain_verified_at)->toBeNull();
});
