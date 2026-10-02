<?php

use App\Services\Platform\HttpHttpsProbe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('serves is true when the health URL answers 2xx over HTTPS', function () {
    Http::fake(['https://shop.example.com/up' => Http::response('', 200)]);

    expect(new HttpHttpsProbe()->serves('shop.example.com'))->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://shop.example.com/up');
});

test('serves is false when the health URL does not answer 2xx', function (int $status) {
    Http::fake(['https://shop.example.com/up' => Http::response('', $status, ['Location' => 'https://elsewhere.example.com/up'])]);

    expect(new HttpHttpsProbe()->serves('shop.example.com'))->toBeFalse();
})->with([
    'redirect to another host' => 301,
    'not found' => 404,
    'server error' => 500,
]);

test('serves is false when the connection fails', function (string $message) {
    Http::fake(['https://shop.example.com/up' => fn () => throw new ConnectionException($message)]);

    expect(new HttpHttpsProbe()->serves('shop.example.com'))->toBeFalse();
})->with([
    'timeout' => 'cURL error 28: Operation timed out',
    'TLS handshake error' => 'cURL error 35: SSL connect error',
    'certificate mismatch' => 'cURL error 60: SSL certificate problem',
]);

test('serves does not follow redirects', function () {
    Http::fake(['https://shop.example.com/up' => Http::response('', 301, ['Location' => 'https://elsewhere.example.com/up'])]);

    new HttpHttpsProbe()->serves('shop.example.com');

    Http::assertSentCount(1);
});
