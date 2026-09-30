<?php

use App\Services\Platform\Contracts\ForgeClient;
use App\Services\Platform\HttpForgeClient;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

const FORGE_CLIENT_DOMAINS_URL = 'https://forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains';

beforeEach(function () {
    Http::preventStrayRequests();
    test()->logger = Log::spy();

    config([
        'services.forge.token' => 'test-token',
        'services.forge.organization' => 'test-organization',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);
});

function forgeDomainListing(string $domain, string $id = '333'): PromiseInterface
{
    return Http::response([
        'data' => [['id' => $id, 'attributes' => ['name' => $domain]]],
        'meta' => ['next_cursor' => null],
    ]);
}

test('it is the container binding for the Forge client contract', function () {
    expect(resolve(ForgeClient::class))->toBeInstanceOf(HttpForgeClient::class);
});

test('obtainSslCertificate posts a Let\'s Encrypt request for the matching domain', function () {
    Http::fake([
        FORGE_CLIENT_DOMAINS_URL.'/333/certificates' => Http::response([], 202),
        FORGE_CLIENT_DOMAINS_URL.'*' => forgeDomainListing('bakery.com'),
    ]);

    $result = (new HttpForgeClient)->obtainSslCertificate('bakery.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === FORGE_CLIENT_DOMAINS_URL.'/333/certificates'
        && $request->data() === ['type' => 'letsencrypt']
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->hasHeader('Accept', 'application/vnd.api+json'));
    test()->logger->shouldHaveReceived('info')->once()->with('Forge: SSL certificate requested', ['domain' => 'bakery.com']);
});

test('obtainSslCertificate logs and returns false when the domain is not in Forge', function () {
    Http::fake([
        FORGE_CLIENT_DOMAINS_URL.'*' => Http::response(['data' => [], 'meta' => ['next_cursor' => null]]),
    ]);

    $result = (new HttpForgeClient)->obtainSslCertificate('missing.com');

    expect($result)->toBeFalse();

    Http::assertSentCount(1);
    test()->logger->shouldHaveReceived('error')->once()->with('Forge: domain not found for SSL request', ['domain' => 'missing.com']);
});

test('obtainSslCertificate returns false and logs when Forge refuses after retrying', function () {
    Http::fake([
        FORGE_CLIENT_DOMAINS_URL.'/333/certificates' => Http::response([], 422),
        FORGE_CLIENT_DOMAINS_URL.'*' => forgeDomainListing('bakery.com'),
    ]);

    $result = (new HttpForgeClient)->obtainSslCertificate('bakery.com');

    expect($result)->toBeFalse();

    Http::assertSentCount(4);
    test()->logger->shouldHaveReceived('error')->once()->withArgs(fn (string $message, array $context): bool => $message === 'Forge: obtainSslCertificate failed'
        && str_contains((string) $context['error'], 'status code 422'));
});

test('addDomainAlias returns false and logs when Forge refuses the domain after retrying', function () {
    Http::fake(fn (Request $request) => $request->method() === 'POST'
        ? Http::response(['errors' => []], 422)
        : Http::response(['data' => [], 'meta' => ['next_cursor' => null]]));

    $result = (new HttpForgeClient)->addDomainAlias('bakery.com');

    expect($result)->toBeFalse();

    Http::assertSentCount(4);
    test()->logger->shouldHaveReceived('error')->once()->withArgs(fn (string $message, array $context): bool => $message === 'Forge: addDomainAlias failed'
        && str_contains((string) $context['error'], 'status code 422'));
});

test('addDomainAlias logs a success when the domain is created', function () {
    Http::fake(fn (Request $request) => $request->method() === 'POST'
        ? Http::response(['data' => ['id' => '333']], 202)
        : Http::response(['data' => [], 'meta' => ['next_cursor' => null]]));

    $result = (new HttpForgeClient)->addDomainAlias('bakery.com');

    expect($result)->toBeTrue();

    test()->logger->shouldHaveReceived('info')->once()->with('Forge: domain added', ['domain' => 'bakery.com']);
});

test('removeDomainAlias returns false when Forge refuses the delete', function () {
    Http::fake([
        FORGE_CLIENT_DOMAINS_URL.'/333' => Http::response([], 500),
        FORGE_CLIENT_DOMAINS_URL.'*' => forgeDomainListing('bakery.com'),
    ]);

    $result = (new HttpForgeClient)->removeDomainAlias('bakery.com');

    expect($result)->toBeFalse();
});

test('domain lookups match the exact domain name among the listed records', function () {
    Http::fake([
        FORGE_CLIENT_DOMAINS_URL.'/444' => Http::response(status: 204),
        FORGE_CLIENT_DOMAINS_URL.'*' => Http::response([
            'data' => [
                ['id' => 333, 'attributes' => ['name' => 'www.bakery.com']],
                ['id' => 444, 'attributes' => ['name' => 'bakery.com']],
            ],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = (new HttpForgeClient)->removeDomainAlias('bakery.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
        && $request->url() === FORGE_CLIENT_DOMAINS_URL.'/444');
});

test('every operation swallows transport errors, logs them and returns false', function (string $method, string $logMessage) {
    Http::fake(fn () => throw new ConnectionException('Connection refused'));

    $result = (new HttpForgeClient)->{$method}('bakery.com');

    expect($result)->toBeFalse();

    test()->logger->shouldHaveReceived('error')->once()->with($logMessage, ['error' => 'Connection refused']);
})->with([
    'addDomainAlias' => ['addDomainAlias', 'Forge: addDomainAlias failed'],
    'obtainSslCertificate' => ['obtainSslCertificate', 'Forge: obtainSslCertificate failed'],
    'removeDomainAlias' => ['removeDomainAlias', 'Forge: removeDomainAlias failed'],
]);
