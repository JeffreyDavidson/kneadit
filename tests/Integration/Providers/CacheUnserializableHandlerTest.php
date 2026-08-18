<?php

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => setUpTenantTest());

test('handler throws in non-production when an unserializable class is read from cache', function () {
    // The array cache driver bypasses serialize/unserialize, so we exercise
    // the handler directly with the same arguments the framework would pass.
    $logger = Log::spy();

    $reflection = new ReflectionClass(CacheRepository::class);
    $handler = $reflection->getStaticPropertyValue('unserializableClassHandler');

    expect($handler)->not->toBeNull('AppServiceProvider should register the handler.');

    expect(fn () => $handler('orders.dashboard.cards', App\Models\Orders\Order::class))
        ->toThrow(RuntimeException::class, 'Cache returned __PHP_Incomplete_Class for key [orders.dashboard.cards]');

    $logger->shouldHaveReceived('error')
        ->with(Mockery::on(fn (string $msg): bool => str_contains($msg, 'orders.dashboard.cards')));
});

test('handler still fires (logs + throws) when class name is unknown', function () {
    $logger = Log::spy();

    $reflection = new ReflectionClass(CacheRepository::class);
    $handler = $reflection->getStaticPropertyValue('unserializableClassHandler');

    expect(fn () => $handler('some.key', null))
        ->toThrow(RuntimeException::class, 'original class: unknown');

    $logger->shouldHaveReceived('error');
});

test('Cache facade still works for primitive values', function () {
    Cache::put('counter', 42);
    expect(Cache::get('counter'))->toBe(42);
});
