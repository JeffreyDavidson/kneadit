<?php

declare(strict_types=1);

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Pings the Redis connections the cache, queue and session actually use. It
 * passes without connecting when none of them is on Redis.
 */
class RedisConnectionCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        $connections = $this->connectionsInUse();

        if ($connections === []) {
            return HealthCheckResult::pass('Redis not in use');
        }

        $failures = [];

        foreach ($connections as $name) {
            $failure = $this->pingFailure($name);

            if ($failure !== null) {
                $failures[] = $failure;
            }
        }

        if ($failures !== []) {
            return HealthCheckResult::fail(implode('; ', $failures));
        }

        return HealthCheckResult::pass('Redis OK ('.implode(', ', $connections).')');
    }

    private function pingFailure(string $name): ?string
    {
        try {
            if (Redis::connection($name)->ping() === false) {
                return "Redis connection '{$name}' did not answer PING";
            }
        } catch (Throwable $exception) {
            return "Redis connection '{$name}' failed: {$exception->getMessage()}";
        }

        return null;
    }

    /** @return list<string> */
    private function connectionsInUse(): array
    {
        $connections = [];

        $store = config()->string('cache.default');

        if (config("cache.stores.{$store}.driver") === 'redis') {
            $connection = config("cache.stores.{$store}.connection");

            $connections[] = $this->name($connection);
            $connections[] = $this->name(config("cache.stores.{$store}.lock_connection") ?? $connection);
        }

        $queue = config()->string('queue.default');

        if (config("queue.connections.{$queue}.driver") === 'redis') {
            $connections[] = $this->name(config("queue.connections.{$queue}.connection"));
        }

        if (config('session.driver') === 'redis') {
            $connections[] = $this->name(config('session.connection') ?? config('cache.stores.redis.connection'));
        }

        return array_values(array_unique($connections));
    }

    private function name(mixed $connection): string
    {
        return is_string($connection) && $connection !== '' ? $connection : 'default';
    }
}
