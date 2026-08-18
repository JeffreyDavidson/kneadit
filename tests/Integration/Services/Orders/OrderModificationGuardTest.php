<?php

use App\Models\Orders\Order;
use App\Services\Orders\OrderModificationGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('cannot modify when window is disabled', function () {
    settings(['order_modification_window_minutes' => 0]);
    $order = Order::factory()->pending()->unpaid()->create();

    expect(resolve(OrderModificationGuard::class)->canModify($order))->toBeFalse();
});

test('cannot modify a confirmed order', function () {
    settings(['order_modification_window_minutes' => 30]);
    $order = Order::factory()->confirmed()->unpaid()->create();

    expect(resolve(OrderModificationGuard::class)->canModify($order))->toBeFalse();
});

test('cannot modify a paid order', function () {
    settings(['order_modification_window_minutes' => 30]);
    $order = Order::factory()->pending()->paid()->create();

    expect(resolve(OrderModificationGuard::class)->canModify($order))->toBeFalse();
});

test('can modify a fresh pending unpaid order within window', function () {
    settings(['order_modification_window_minutes' => 30]);
    $order = Order::factory()->pending()->unpaid()->create();

    expect(resolve(OrderModificationGuard::class)->canModify($order))->toBeTrue();
});

test('cannot modify after window has expired', function () {
    settings(['order_modification_window_minutes' => 10]);
    $order = Order::factory()->pending()->unpaid()->create([
        'created_at' => now()->subMinutes(15),
    ]);

    expect(resolve(OrderModificationGuard::class)->canModify($order))->toBeFalse();
});

test('reports remaining minutes correctly', function () {
    settings(['order_modification_window_minutes' => 30]);
    $order = Order::factory()->pending()->unpaid()->create([
        'created_at' => now()->subMinutes(10),
    ]);

    expect(resolve(OrderModificationGuard::class)->minutesRemaining($order))->toBe(20);
});
