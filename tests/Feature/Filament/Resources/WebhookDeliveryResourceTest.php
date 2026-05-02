<?php

use App\Filament\Resources\WebhookDeliveries\Pages\ListWebhookDeliveries;
use App\Filament\Resources\WebhookDeliveries\WebhookDeliveryResource;
use App\Models\Operations\WebhookDelivery;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('lists webhook delivery rows', function () {
    $rows = WebhookDelivery::factory()->count(3)->create();

    Livewire::test(ListWebhookDeliveries::class)
        ->assertCanSeeTableRecords($rows);
});

test('most-recent dispatch appears first by default', function () {
    $old = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(5)]);
    $new = WebhookDelivery::factory()->create(['dispatched_at' => now()]);

    Livewire::test(ListWebhookDeliveries::class)
        ->assertCanSeeTableRecords([$new, $old], inOrder: true);
});

test('succeeded ternary filter narrows to failed only', function () {
    $ok = WebhookDelivery::factory()->succeeded()->create();
    $fail = WebhookDelivery::factory()->failed()->create();

    Livewire::test(ListWebhookDeliveries::class)
        ->filterTable('succeeded', false)
        ->assertCanSeeTableRecords([$fail])
        ->assertCanNotSeeTableRecords([$ok]);
});

test('event select filter narrows to a single event', function () {
    $created = WebhookDelivery::factory()->create(['event' => 'order.created']);
    $updated = WebhookDelivery::factory()->create(['event' => 'order.updated']);

    Livewire::test(ListWebhookDeliveries::class)
        ->filterTable('event', 'order.created')
        ->assertCanSeeTableRecords([$created])
        ->assertCanNotSeeTableRecords([$updated]);
});

test('does not expose create/edit/delete affordances', function () {
    expect(WebhookDeliveryResource::canCreate())->toBeFalse();

    $row = WebhookDelivery::factory()->create();
    expect(WebhookDeliveryResource::canEdit($row))->toBeFalse()
        ->and(WebhookDeliveryResource::canDelete($row))->toBeFalse();
});
