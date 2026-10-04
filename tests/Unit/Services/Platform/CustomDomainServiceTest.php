<?php

use App\Enums\Platform\DomainCheck;
use App\Services\Platform\CustomDomainService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();

    config([
        'services.forge.server_ip' => '203.0.113.10',
        'services.forge.token' => 'test-token',
        'services.forge.organization' => 'test-organization',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);
});

test('serverIp returns the configured Forge server address', function () {
    expect(resolve(CustomDomainService::class)->serverIp())->toBe('203.0.113.10');
});

test('isValidFormat accepts well-formed domains', function (string $domain) {
    expect(resolve(CustomDomainService::class)->isValidFormat($domain))->toBeTrue();
})->with([
    'apex domain' => 'bakery.com',
    'subdomain' => 'shop.bakery.com',
    'hyphenated label' => 'my-bakery.co.uk',
    'digits in the label' => 'bakery24.org',
    'single character label' => 'a.io',
]);

test('isValidFormat rejects malformed domains', function (string $domain) {
    expect(resolve(CustomDomainService::class)->isValidFormat($domain))->toBeFalse();
})->with([
    'empty string' => '',
    'no top-level domain' => 'bakery',
    'numeric top-level domain' => 'bakery.123',
    'one letter top-level domain' => 'bakery.c',
    'leading hyphen' => '-bakery.com',
    'trailing hyphen' => 'bakery-.com',
    'underscore' => 'my_bakery.com',
    'embedded space' => 'my bakery.com',
    'leading dot' => '.bakery.com',
    'trailing dot' => 'bakery.com.',
    'scheme included' => 'https://bakery.com',
    'path included' => 'bakery.com/menu',
    'port included' => 'bakery.com:8080',
]);

test('isDnsVerified is true when the domain resolves to the server address', function () {
    config(['services.forge.server_ip' => '127.0.0.1']);

    expect(resolve(CustomDomainService::class)->isDnsVerified('localhost'))->toBeTrue();
});

test('isDnsVerified is false when the domain resolves to a different address', function () {
    expect(resolve(CustomDomainService::class)->isDnsVerified('localhost'))->toBeFalse();
});

test('verify reports which part of the check failed', function (bool $dnsOk, bool $httpsOk, DomainCheck $expected) {
    fakeDnsRecords(['shop.example.com' => $dnsOk ? '203.0.113.10' : '198.51.100.7']);
    fakeHttpsProbe(['shop.example.com' => $httpsOk]);

    expect(resolve(CustomDomainService::class)->verify('shop.example.com'))->toBe($expected);
})->with([
    'DNS and HTTPS ok' => [true, true, DomainCheck::Verified],
    'DNS ok, HTTPS failing' => [true, false, DomainCheck::HttpsUnavailable],
    'proxied, proof ok' => [false, true, DomainCheck::VerifiedThroughProxy],
    'not pointing here and no proof' => [false, false, DomainCheck::DnsMissing],
]);

test('verify treats a domain with no DNS record and no proof as not pointing here', function () {
    fakeDnsRecords([]);
    fakeHttpsProbe([]);

    expect(resolve(CustomDomainService::class)->verify('shop.example.com'))->toBe(DomainCheck::DnsMissing);
});

test('hasOwnershipProof follows the HTTPS probe', function (bool $serves) {
    fakeHttpsProbe(['shop.example.com' => $serves]);

    expect(resolve(CustomDomainService::class)->hasOwnershipProof('shop.example.com'))->toBe($serves);
})->with([true, false]);

test('provisionSsl returns null without calling Forge when it is not configured', function () {
    config(['services.forge.token' => '']);

    $result = resolve(CustomDomainService::class)->provisionSsl('bakery.com');

    expect($result)->toBeNull();

    Http::assertNothingSent();
});

test('provisionSsl requests a Let\'s Encrypt certificate when Forge is configured', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333/certificates' => Http::response([], 202),
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [['id' => '333', 'attributes' => ['name' => 'bakery.com']]],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(CustomDomainService::class)->provisionSsl('bakery.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && str_ends_with($request->url(), '/domains/333/certificates')
        && $request->data() === ['type' => 'letsencrypt']);
});

test('provisionSsl returns false when Forge rejects the certificate request', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333/certificates' => Http::response([], 422),
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [['id' => '333', 'attributes' => ['name' => 'bakery.com']]],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(CustomDomainService::class)->provisionSsl('bakery.com');

    expect($result)->toBeFalse();
});

test('provisionSsl returns false when the domain is unknown to Forge', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(CustomDomainService::class)->provisionSsl('bakery.com');

    expect($result)->toBeFalse();
});
