<?php

use App\Filament\Central\Resources\TenantResource\Pages\EditTenant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Platform\Contracts\ForgeClient;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    config(['services.forge.api_token' => null, 'services.forge.server_ip' => '203.0.113.10']);
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('saving the form does not change the custom domain', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->fillForm(['custom_domain' => 'sneaky.example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tenant->refresh()->custom_domain)->toBe('shop.example.com');
});

test('the custom domain field is read only and shows the verification status', function () {
    Date::setTestNow('2026-10-01 09:30');
    $verified = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()]);
    $unverified = Tenant::factory()->create(['custom_domain' => 'cakes.example.com']);

    livewire(EditTenant::class, ['record' => $verified->getKey()])
        ->assertFormFieldIsDisabled('custom_domain')
        ->assertSee('Verified Oct 1, 2026');

    livewire(EditTenant::class, ['record' => $unverified->getKey()])
        ->assertSee('Not verified');
});

test('setting a domain another bakery owns shows a validation error and saves nothing', function () {
    Tenant::factory()->create()->createDomain(['domain' => 'taken.example.com']);
    $tenant = Tenant::factory()->create();

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('setCustomDomain', ['custom_domain' => 'taken.example.com'])
        ->assertHasFormErrors(['custom_domain']);

    expect($tenant->refresh()->custom_domain)->toBeNull()
        ->and(Domain::query()->where('tenant_id', $tenant->id)->exists())->toBeFalse();
});

test('setting a valid domain saves it, creates the domain record and clears the verification', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => 'old.example.com', 'custom_domain_verified_at' => now()]);
    $tenant->createDomain(['domain' => 'old.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('setCustomDomain', ['custom_domain' => 'HTTPS://Shop.Example.com/'])
        ->assertHasNoFormErrors()
        ->assertNotified('Custom domain saved');

    expect($tenant->refresh()->custom_domain)->toBe('shop.example.com')
        ->and($tenant->custom_domain_verified_at)->toBeNull()
        ->and(Domain::query()->where('domain', 'shop.example.com')->where('tenant_id', $tenant->id)->exists())->toBeTrue()
        ->and(Domain::query()->where('domain', 'old.example.com')->exists())->toBeFalse();
});

test('removing the domain clears it and deletes the domain record', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()]);
    $tenant->createDomain(['domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('removeCustomDomain')
        ->assertNotified('Custom domain removed');

    expect($tenant->refresh()->custom_domain)->toBeNull()
        ->and($tenant->custom_domain_verified_at)->toBeNull()
        ->and(Domain::query()->where('domain', 'shop.example.com')->exists())->toBeFalse();
});

test('the remove and verify actions are hidden while the bakery has no custom domain', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => null]);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->assertActionHidden('removeCustomDomain')
        ->assertActionHidden('verifyCustomDomain')
        ->assertActionVisible('setCustomDomain');
});

test('verifying the domain records the verification time when the domain points at the server', function () {
    Date::setTestNow('2026-10-01 09:30');
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => true]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('verifyCustomDomain')
        ->assertNotified('Domain verified');

    expect($tenant->refresh()->custom_domain_verified_at->toDateTimeString())->toBe('2026-10-01 09:30:00');
});

test('verifying a proxied domain reports it as verified through the proxy', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    fakeDnsRecords(['shop.example.com' => '104.21.0.1']);
    fakeHttpsProbe(['shop.example.com' => true]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('verifyCustomDomain')
        ->assertNotified('Domain verified');

    expect($tenant->refresh()->custom_domain_verified_at)->not->toBeNull();
});

test('verifying the domain warns and leaves the domain unverified when it does not point at the server', function () {
    fakeDnsRecords(['shop.example.com' => '198.51.100.7']);
    fakeHttpsProbe([]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('verifyCustomDomain')
        ->assertNotified('DNS not configured');

    expect($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('verifying the domain reports a missing HTTPS certificate when only HTTPS fails', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    fakeHttpsProbe(['shop.example.com' => false]);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()]);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('verifyCustomDomain')
        ->assertNotified('DNS OK, no HTTPS certificate yet');

    expect($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('platform admins can request an SSL certificate for the bakery domain', function () {
    config(['services.forge.token' => 't', 'services.forge.organization' => 'o', 'services.forge.server_id' => '1', 'services.forge.site_id' => '2']);
    $forge = Mockery::mock(ForgeClient::class);
    $forge->expects('obtainSslCertificate')->with('shop.example.com')->andReturn(true);
    app()->instance(ForgeClient::class, $forge);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->callAction('requestSslCertificate')
        ->assertNotified('SSL certificate requested');
});

test('the SSL certificate action is hidden while the bakery has no custom domain', function () {
    $tenant = Tenant::factory()->create(['custom_domain' => null]);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->assertActionHidden('requestSslCertificate');
});

test('platform admins can pause a bakery from its edit page', function () {
    Date::setTestNow('2026-10-05 09:30');
    $tenant = Tenant::factory()->create(['storefront_enabled' => false, 'external_website' => 'https://own-site.example.com']);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->assertActionHidden('resume')
        ->callAction('pause')
        ->assertNotified('Bakery paused');

    expect($tenant->refresh()->paused_at?->toDateTimeString())->toBe('2026-10-05 09:30:00')
        ->and($tenant->storefront_enabled)->toBeFalse();
});

test('platform admins can resume a paused bakery from its edit page', function () {
    $tenant = Tenant::factory()->create(['paused_at' => now()->subDay()]);

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->assertActionHidden('pause')
        ->callAction('resume')
        ->assertNotified('Bakery resumed');

    expect($tenant->refresh()->paused_at)->toBeNull();
});
