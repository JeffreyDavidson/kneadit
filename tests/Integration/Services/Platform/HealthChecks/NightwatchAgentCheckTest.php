<?php

use App\Services\Platform\HealthChecks\NightwatchAgentCheck;

/** @return array{resource, string} a local TCP listener and its host:port address */
function listenOnLocalPort(): array
{
    $server = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

    throw_unless(is_resource($server), RuntimeException::class, "Could not listen: {$errorMessage}");

    return [$server, stream_socket_get_name($server, false)];
}

test('it passes without connecting when Nightwatch is disabled', function () {
    config(['nightwatch.enabled' => false, 'nightwatch.ingest.uri' => '127.0.0.1:1']);

    $result = (new NightwatchAgentCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Nightwatch disabled');
});

test('it passes when something accepts a connection on the ingest address', function () {
    [$server, $address] = listenOnLocalPort();
    config(['nightwatch.enabled' => true, 'nightwatch.ingest.uri' => $address]);

    $result = (new NightwatchAgentCheck)->run();

    fclose($server);

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe("Nightwatch agent listening on {$address}");
});

test('it fails when nothing is listening on the ingest address', function () {
    [$server, $address] = listenOnLocalPort();
    fclose($server);
    config(['nightwatch.enabled' => true, 'nightwatch.ingest.uri' => $address]);

    $result = (new NightwatchAgentCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toStartWith("Nightwatch agent not listening on {$address}");
});

test('it fails when no ingest address is configured', function (?string $uri) {
    config(['nightwatch.enabled' => true, 'nightwatch.ingest.uri' => $uri]);

    $result = (new NightwatchAgentCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Nightwatch agent not listening: no ingest address configured');
})->with([
    'missing' => [null],
    'empty' => [''],
]);
