<?php

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    setUpCentralTest();

    // Mirror production: the tenant domain is not a central domain.
    config([
        'tenancy.central_domains' => ['app.getkneadit.app'],
        'tenancy.tenant_domain' => 'getkneadit.app',
    ]);

    Tenant::factory()
        ->create(['id' => 'bakery-on-biscotto', 'subdomain' => 'bakery-on-biscotto'])
        ->domains()
        ->create(['domain' => 'bakery-on-biscotto']);
});

test('the new subdomain resolves the bakery and the old one redirects to it', function () {
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto'])
        ->assertSuccessful();

    get('https://bakeryonbiscotto.getkneadit.app/')
        ->assertOk();
    get('https://bakery-on-biscotto.getkneadit.app/menu?x=1')
        ->assertStatus(301)
        ->assertRedirect('https://bakeryonbiscotto.getkneadit.app/menu?x=1');
});

test('only get and head requests on the old subdomain are redirected', function () {
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto'])
        ->assertSuccessful();

    $response = post('https://bakery-on-biscotto.getkneadit.app/menu');

    expect($response->status())->not->toBe(301)
        ->and($response->isRedirect())->toBeFalse();
});

test('a custom domain is never redirected', function () {
    DB::table('domains')->insert([
        'domain' => 'bakeryonbiscotto.com',
        'tenant_id' => 'bakery-on-biscotto',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto'])
        ->assertSuccessful();

    get('https://bakeryonbiscotto.com/')
        ->assertOk();
});

test('a bakery that never changed its subdomain is not redirected', function () {
    get('https://bakery-on-biscotto.getkneadit.app/')
        ->assertOk();
});

test('refuses an unusable subdomain with the onboarding message', function () {
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'Bad_Name'])
        ->expectsOutputToContain('Use lowercase letters, numbers and hyphens')
        ->assertFailed();

    expect(DB::table('tenants')->where('id', 'bakery-on-biscotto')->value('subdomain'))->toBe('bakery-on-biscotto');
});

test('fails clearly for an unknown bakery', function () {
    artisan('tenants:change-subdomain', ['tenant' => 'nobody', 'subdomain' => 'bakeryonbiscotto'])
        ->expectsOutputToContain('No bakery with id "nobody"')
        ->assertFailed();
});

test('running it twice is a no-op', function () {
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto'])
        ->assertSuccessful();
    artisan('tenants:change-subdomain', ['tenant' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto'])
        ->expectsOutputToContain('already')
        ->assertSuccessful();

    expect(DB::table('domains')->where('domain', 'bakeryonbiscotto')->count())->toBe(1);
});
