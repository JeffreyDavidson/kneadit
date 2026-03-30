<?php

use App\Filament\Resources\Holidays\Pages\ListHolidays;
use App\Models\Holiday;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list holidays in the table', function () {
    $holidays = Holiday::factory()->count(3)->create();

    Livewire::test(ListHolidays::class)
        ->assertCanSeeTableRecords($holidays);
});

test('can create a holiday via slide-over', function () {
    Livewire::test(ListHolidays::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Christmas',
            'date' => '2026-12-25',
            'order_deadline' => '2026-12-20',
            'is_closed' => true,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Holiday::class, [
        'name' => 'Christmas',
    ]);
});

test('can edit a holiday via table action', function () {
    $holiday = Holiday::factory()->create();

    Livewire::test(ListHolidays::class)
        ->callAction(TestAction::make('edit')->table($holiday), data: [
            'name' => 'Updated Holiday',
            'date' => $holiday->date->format('Y-m-d'),
            'order_deadline' => $holiday->order_deadline->format('Y-m-d'),
        ])
        ->assertHasNoFormErrors();

    expect($holiday->fresh()->name)->toBe('Updated Holiday');
});

test('can render holiday table columns', function (string $column) {
    Holiday::factory()->create();

    Livewire::test(ListHolidays::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'date', 'order_deadline']);

test('create holiday validates required fields', function (array $data, array $errors) {
    Livewire::test(ListHolidays::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Test',
            'date' => '2026-12-25',
            'order_deadline' => '2026-12-20',
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'date is required' => [['date' => null], ['date' => 'required']],
    'order deadline is required' => [['order_deadline' => null], ['order_deadline' => 'required']],
]);

test('can search holidays by name', function () {
    $christmas = Holiday::factory()->create(['name' => 'Christmas']);
    $easter = Holiday::factory()->create(['name' => 'Easter']);

    Livewire::test(ListHolidays::class)
        ->searchTable('Christmas')
        ->assertCanSeeTableRecords(collect([$christmas]))
        ->assertCanNotSeeTableRecords(collect([$easter]));
});

test('can filter holidays by active status', function () {
    $active = Holiday::factory()->create(['is_active' => true]);
    $inactive = Holiday::factory()->create(['is_active' => false]);

    Livewire::test(ListHolidays::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});

test('can sort holidays by date', function () {
    $early = Holiday::factory()->create(['date' => '2026-03-01']);
    $late = Holiday::factory()->create(['date' => '2026-12-25']);

    Livewire::test(ListHolidays::class)
        ->sortTable('date')
        ->assertCanSeeTableRecords(collect([$early, $late]), inOrder: true)
        ->sortTable('date', 'desc')
        ->assertCanSeeTableRecords(collect([$late, $early]), inOrder: true);
});
