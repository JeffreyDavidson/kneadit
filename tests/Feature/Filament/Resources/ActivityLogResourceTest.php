<?php

use App\Enums\Operations\ActivityAction;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\Operations\ActivityLog;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('lists activity log rows', function () {
    $rows = ActivityLog::factory()->count(3)->create();

    livewire(ListActivityLogs::class)
        ->assertCanSeeTableRecords($rows);
});

test('newest rows appear first by default', function () {
    $old = ActivityLog::factory()->create(['created_at' => now()->subDays(5)]);
    $new = ActivityLog::factory()->create(['created_at' => now()]);

    livewire(ListActivityLogs::class)
        ->assertCanSeeTableRecords([$new, $old], inOrder: true);
});

test('action filter narrows to a single ActivityAction', function () {
    $created = ActivityLog::factory()->create(['action' => ActivityAction::Created]);
    $deleted = ActivityLog::factory()->create(['action' => ActivityAction::Deleted]);

    livewire(ListActivityLogs::class)
        ->filterTable('action', ActivityAction::Deleted->value)
        ->assertCanSeeTableRecords([$deleted])
        ->assertCanNotSeeTableRecords([$created]);
});

test('does not expose create/edit/delete affordances', function () {
    livewire(ListActivityLogs::class)
        ->assertOk();

    expect(ActivityLogResource::canCreate())->toBeFalse();
});
