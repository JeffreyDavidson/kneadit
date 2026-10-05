<?php

use App\Services\Platform\HealthChecks\RedisConnectionCheck;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

/** Makes the named Redis connections answer PING, or throw the given error when they are down. */
function fakeRedisConnections(array $answering, array $refusing = []): void
{
    foreach ($answering as $name) {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('ping')->once()->andReturn(true);

        Redis::shouldReceive('connection')->with($name)->andReturn($connection);
    }

    foreach ($refusing as $name) {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('ping')->once()->andThrow(new RuntimeException('Connection refused'));

        Redis::shouldReceive('connection')->with($name)->andReturn($connection);
    }
}

test('it passes without touching Redis when nothing uses it', function () {
    Redis::shouldReceive('connection')->never();

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Redis not in use');
});

test('it passes when the Redis connection behind the cache answers PING', function () {
    config(['cache.default' => 'redis']);
    fakeRedisConnections(['cache', 'default']);

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Redis OK (cache, default)');
});

test('it fails when the Redis connection behind the cache refuses connections', function () {
    config(['cache.default' => 'redis']);
    fakeRedisConnections(['default'], ['cache']);

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe("Redis connection 'cache' failed: Connection refused");
});

test('it fails when PING is answered with false', function () {
    config(['queue.default' => 'redis']);
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('ping')->once()->andReturn(false);
    Redis::shouldReceive('connection')->with('default')->andReturn($connection);

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe("Redis connection 'default' did not answer PING");
});

test('it checks the connection of a queue or session that uses Redis', function (array $config, string $connection) {
    config($config);
    fakeRedisConnections([$connection]);

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe("Redis OK ({$connection})");
})->with([
    'queue' => [['queue.default' => 'redis', 'queue.connections.redis.connection' => 'queue-redis'], 'queue-redis'],
    'session on the default connection' => [['session.driver' => 'redis', 'session.connection' => null, 'cache.stores.redis.connection' => 'default'], 'default'],
    'session on its own connection' => [['session.driver' => 'redis', 'session.connection' => 'sessions'], 'sessions'],
]);

test('it checks each connection once when several things share it', function () {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'default',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
    ]);
    fakeRedisConnections(['default']);

    $result = (new RedisConnectionCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Redis OK (default)');
});
