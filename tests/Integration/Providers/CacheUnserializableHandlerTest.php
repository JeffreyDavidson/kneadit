<?php

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Psr\Log\LoggerInterface;

beforeEach(fn () => setUpTenantTest());

test('handler throws in non-production when an unserializable class is read from cache', function () {
    // The array cache driver bypasses serialize/unserialize, so we exercise
    // the handler directly with the same arguments the framework would pass.
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('error')
        ->with(Argument::satisfies(fn (mixed $message): bool => is_string($message) && str_contains($message, 'orders.dashboard.cards')))
        ->times(1);
    Log::swap($logger);

    $reflection = new ReflectionClass(CacheRepository::class);
    $handler = $reflection->getStaticPropertyValue('unserializableClassHandler');

    if (! is_callable($handler)) {
        throw new RuntimeException('AppServiceProvider should register the handler.');
    }

    expect(fn () => $handler('orders.dashboard.cards', App\Models\Orders\Order::class))
        ->toThrow(RuntimeException::class, 'Cache returned __PHP_Incomplete_Class for key [orders.dashboard.cards]');

});

test('handler still fires (logs + throws) when class name is unknown', function () {
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('error')->times(1);
    Log::swap($logger);

    $reflection = new ReflectionClass(CacheRepository::class);
    $handler = $reflection->getStaticPropertyValue('unserializableClassHandler');

    if (! is_callable($handler)) {
        throw new RuntimeException('AppServiceProvider should register the handler.');
    }

    expect(fn () => $handler('some.key', null))
        ->toThrow(RuntimeException::class, 'original class: unknown');

});

test('Cache facade still works for primitive values', function () {
    Cache::put('counter', 42);
    expect(Cache::get('counter'))->toBe(42);
});
