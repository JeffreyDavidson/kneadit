<?php

use App\Builders\Operations\WebhookDeliveryQueryBuilder;
use App\Models\Operations\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

it('returns a custom WebhookDeliveryQueryBuilder from query()', function () {
    expect(WebhookDelivery::query())->toBeInstanceOf(WebhookDeliveryQueryBuilder::class);
});

test('failed scope filters to unsuccessful deliveries', function () {
    WebhookDelivery::factory()->succeeded()->create();
    $failed = WebhookDelivery::factory()->failed()->create();

    expect(WebhookDelivery::query()->failed()->pluck('id')->all())->toBe([$failed->id]);
});

test('successful scope filters to succeeded deliveries', function () {
    $ok = WebhookDelivery::factory()->succeeded()->create();
    WebhookDelivery::factory()->failed()->create();

    expect(WebhookDelivery::query()->successful()->pluck('id')->all())->toBe([$ok->id]);
});

test('forEvent scope filters to a single event name', function () {
    $created = WebhookDelivery::factory()->create(['event' => 'order.created']);
    WebhookDelivery::factory()->create(['event' => 'order.updated']);

    expect(WebhookDelivery::query()->forEvent('order.created')->pluck('id')->all())->toBe([$created->id]);
});

test('recent scope filters to deliveries within the cutoff window', function () {
    $recent = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(2)]);
    WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(60)]);

    expect(WebhookDelivery::query()->recent(30)->pluck('id')->all())->toBe([$recent->id]);
});
