<?php

use App\Services\Platform\CustomDomainProof;
use App\Services\Platform\HttpHttpsProbe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const PROOF_URL = 'https://shop.example.com/.well-known/kneadit-domain-proof';

beforeEach(function () {
    Http::preventStrayRequests();
});

function probe(): HttpHttpsProbe
{
    return new HttpHttpsProbe(new CustomDomainProof);
}

test('proves is true when the domain answers with its own proof over HTTPS', function () {
    Http::fake([PROOF_URL => Http::response(new CustomDomainProof()->for('shop.example.com'), 200)]);

    expect(probe()->proves('shop.example.com'))->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->url() === PROOF_URL);
});

test('proves is false when the answer is not the domain proof', function (string $body) {
    Http::fake([PROOF_URL => Http::response($body, 200)]);

    expect(probe()->proves('shop.example.com'))->toBeFalse();
})->with([
    'empty 200' => '',
    'health style body' => 'OK',
    'proof for another host' => fn () => new CustomDomainProof()->for('other.example.com'),
]);

test('proves is false when the answer is not 2xx', function (int $status) {
    Http::fake([PROOF_URL => Http::response(new CustomDomainProof()->for('shop.example.com'), $status, ['Location' => 'https://elsewhere.example.com/'])]);

    expect(probe()->proves('shop.example.com'))->toBeFalse();
})->with([
    'redirect to another host' => 301,
    'not found' => 404,
    'server error' => 500,
]);

test('proves is false when the connection fails', function (string $message) {
    Http::fake([PROOF_URL => fn () => throw new ConnectionException($message)]);

    expect(probe()->proves('shop.example.com'))->toBeFalse();
})->with([
    'timeout' => 'cURL error 28: Operation timed out',
    'TLS handshake error' => 'cURL error 35: SSL connect error',
    'certificate mismatch' => 'cURL error 60: SSL certificate problem',
]);

test('proves does not follow redirects', function () {
    Http::fake([PROOF_URL => Http::response('', 301, ['Location' => 'https://elsewhere.example.com/'])]);

    probe()->proves('shop.example.com');

    Http::assertSentCount(1);
});
