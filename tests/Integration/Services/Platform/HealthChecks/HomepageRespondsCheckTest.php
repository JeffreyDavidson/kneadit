<?php

use App\Services\Platform\HealthChecks\HomepageRespondsCheck;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();

    config(['app.url' => 'https://kneadit.example']);
});

test('it passes when the homepage responds successfully', function (int $status) {
    Http::fake(['kneadit.example*' => Http::response('ok', $status)]);

    $result = (new HomepageRespondsCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe("Homepage responds ({$status})");
})->with([
    'ok' => 200,
    'no content' => 204,
]);

test('it requests the configured application url', function () {
    Http::fake(['kneadit.example*' => Http::response('ok')]);

    (new HomepageRespondsCheck)->run();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && rtrim($request->url(), '/') === 'https://kneadit.example');
});

test('it fails when the homepage returns an error status', function (int $status) {
    Http::fake(['kneadit.example*' => Http::response('nope', $status)]);

    $result = (new HomepageRespondsCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toStartWith('Homepage unreachable: ')
        ->toContain((string) $status);
})->with([
    'server error' => 500,
    'service unavailable' => 503,
    'not found' => 404,
]);

test('it fails when the homepage cannot be reached', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $result = (new HomepageRespondsCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Homepage unreachable: Connection timed out');
});
