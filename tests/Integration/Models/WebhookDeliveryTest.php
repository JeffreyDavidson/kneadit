<?php

use App\Models\Operations\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('payload is cast to array', function () {
    $delivery = WebhookDelivery::factory()->create([
        'payload' => ['event' => 'order.created', 'data' => ['order_number' => 'ORD-001']],
    ]);

    expect($delivery->refresh()->payload)
        ->toBeArray()
        ->toMatchArray(['event' => 'order.created']);
});

test('succeeded is cast to bool', function () {
    $delivery = WebhookDelivery::factory()->succeeded()->create();

    expect($delivery->refresh()->succeeded)->toBeTrue();
});

test('dispatched_at is cast to Carbon', function () {
    $delivery = WebhookDelivery::factory()->create();

    expect($delivery->dispatched_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});
