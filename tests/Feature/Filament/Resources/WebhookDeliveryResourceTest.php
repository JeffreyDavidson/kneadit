<?php

use App\Filament\Resources\WebhookDeliveries\Pages\ListWebhookDeliveries;
use App\Filament\Resources\WebhookDeliveries\WebhookDeliveryResource;
use App\Models\Operations\WebhookDelivery;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('lists webhook delivery rows', function () {
    $rows = WebhookDelivery::factory()->count(3)->create();

    livewire(ListWebhookDeliveries::class)
        ->assertCanSeeTableRecords($rows);
});

test('most-recent dispatch appears first by default', function () {
    $old = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(5)]);
    $new = WebhookDelivery::factory()->create(['dispatched_at' => now()]);

    livewire(ListWebhookDeliveries::class)
        ->assertCanSeeTableRecords([$new, $old], inOrder: true);
});

test('succeeded ternary filter narrows to failed only', function () {
    $ok = WebhookDelivery::factory()->succeeded()->create();
    $fail = WebhookDelivery::factory()->failed()->create();

    livewire(ListWebhookDeliveries::class)
        ->filterTable('succeeded', false)
        ->assertCanSeeTableRecords([$fail])
        ->assertCanNotSeeTableRecords([$ok]);
});

test('event select filter narrows to a single event', function () {
    $created = WebhookDelivery::factory()->create(['event' => 'order.created']);
    $updated = WebhookDelivery::factory()->create(['event' => 'order.updated']);

    livewire(ListWebhookDeliveries::class)
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

test('only owners can open the deliveries resource', function (string $role, bool $allowed) {
    test()->actingAs(User::factory()->{$role}()->create());

    expect(WebhookDeliveryResource::canAccess())->toBe($allowed);

    $response = livewire(ListWebhookDeliveries::class);

    $allowed ? $response->assertOk() : $response->assertForbidden();
})->with([
    'owner' => ['owner', true],
    'manager' => ['manager', false],
    'staff' => ['staff', false],
]);

test('only owners hold the manage-webhooks ability', function (string $role, bool $allowed) {
    $user = User::factory()->{$role}()->create();

    expect($user->can('manage-webhooks'))->toBe($allowed);
})->with([
    'owner' => ['owner', true],
    'manager' => ['manager', false],
    'staff' => ['staff', false],
]);

test('owners see the redeliver action', function () {
    $delivery = WebhookDelivery::factory()->create();

    livewire(ListWebhookDeliveries::class)
        ->assertTableActionVisible('redeliver', $delivery);
});
