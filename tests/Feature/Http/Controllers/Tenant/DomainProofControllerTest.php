<?php

use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainProof;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['app.kneadit.test'], 'tenancy.tenant_domain' => 'kneadit.test']);
});

test('a custom domain answers with the proof for its own host', function () {
    $tenant = Tenant::factory()->create(['id' => 'proofbakery']);
    $tenant->domains()->create(['domain' => 'shop.example.com']);

    $response = get('https://shop.example.com/.well-known/kneadit-domain-proof');

    $response->assertOk();
    expect($response->getContent())->toBe(resolve(CustomDomainProof::class)->for('shop.example.com'))
        ->and($response->headers->get('Cache-Control'))->toContain('no-store')->toContain('private');
});

test('the proof differs per host', function () {
    expect(resolve(CustomDomainProof::class)->for('a.example.com'))
        ->not->toBe(resolve(CustomDomainProof::class)->for('b.example.com'));
});

test('the proof still answers while the storefront is disabled', function () {
    $tenant = Tenant::factory()->create(['id' => 'pausedbakery', 'storefront_enabled' => false]);
    $tenant->domains()->create(['domain' => 'paused.example.com']);

    get('https://paused.example.com/.well-known/kneadit-domain-proof')->assertOk()->assertSeeHtml(resolve(CustomDomainProof::class)->for('paused.example.com'));
});

test('the proof is not served on a central domain', function () {
    get('https://app.kneadit.test/.well-known/kneadit-domain-proof')->assertNotFound();
});
