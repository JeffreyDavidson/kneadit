<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Throwable;

/**
 * When Nightwatch is enabled, checks that its local agent accepts a TCP
 * connection on the ingest address. Nothing is sent. Without the agent the app
 * keeps working but reports nothing, which goes unnoticed.
 */
class NightwatchAgentCheck implements HealthCheck
{
    private const float CONNECT_TIMEOUT_SECONDS = 1.0;

    public function run(): HealthCheckResult
    {
        if (config('nightwatch.enabled') !== true) {
            return HealthCheckResult::pass('Nightwatch disabled');
        }

        $uri = config('nightwatch.ingest.uri');
        $address = is_string($uri) ? $uri : '';

        if ($address === '') {
            return HealthCheckResult::fail('Nightwatch agent not listening: no ingest address configured');
        }

        try {
            $connection = stream_socket_client("tcp://{$address}", $errorCode, $errorMessage, self::CONNECT_TIMEOUT_SECONDS);
        } catch (Throwable $exception) {
            // Laravel turns the connection warning into an exception, and it carries the reason.
            return HealthCheckResult::fail("Nightwatch agent not listening on {$address}: {$exception->getMessage()}");
        }

        if ($connection === false) {
            return HealthCheckResult::fail("Nightwatch agent not listening on {$address}: {$errorMessage}");
        }

        fclose($connection);

        return HealthCheckResult::pass("Nightwatch agent listening on {$address}");
    }
}
