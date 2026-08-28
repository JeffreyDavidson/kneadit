<?php

use App\Enums\Financial\IncomeSource;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Models\Financial\Income;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list incomes in the table', function () {
    $incomes = Income::factory()->count(3)->create();

    livewire(ListIncomes::class)
        ->assertCanSeeTableRecords($incomes);
});

test('can create an income via slide-over', function () {
    livewire(ListIncomes::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Farmers market sales',
            'amount' => 350.00,
            'source' => IncomeSource::FarmersMarket->value,
            'date' => '2026-03-26',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Income::class, [
        'description' => 'Farmers market sales',
    ]);
});

test('can render income table columns', function () {
    Income::factory()->create();

    livewire(ListIncomes::class)
        ->assertCanRenderTableColumn('date')
        ->assertCanRenderTableColumn('description')
        ->assertCanRenderTableColumn('source')
        ->assertCanRenderTableColumn('amount');
});

test('can edit an income via table action', function () {
    $income = Income::factory()->create();

    livewire(ListIncomes::class)
        ->callAction(TestAction::make('edit')->table($income), data: [
            'description' => 'Updated income',
            'amount' => $income->amount->dollars(),
            'source' => $income->source->value,
        ])
        ->assertHasNoFormErrors();

    expect($income->fresh()->description)->toBe('Updated income');
});

test('create income validates required fields', function () {
    $cases = [
        [['description' => null], ['description' => 'required']],
        [['amount' => null], ['amount' => 'required']],
        [['source' => null], ['source' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListIncomes::class)
            ->callAction(CreateAction::class, data: [
                'description' => 'Test',
                'amount' => 100,
                'source' => IncomeSource::FarmersMarket->value,
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can search incomes by description', function () {
    $target = Income::factory()->create(['description' => 'Market sales']);
    $other = Income::factory()->create(['description' => 'Online order']);

    livewire(ListIncomes::class)
        ->searchTable('Market')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort incomes by amount', function () {
    $small = Income::factory()->create(['amount' => 25]);
    $large = Income::factory()->create(['amount' => 800]);

    livewire(ListIncomes::class)
        ->sortTable('amount')
        ->assertCanSeeTableRecords(collect([$small, $large]), inOrder: true)
        ->sortTable('amount', 'desc')
        ->assertCanSeeTableRecords(collect([$large, $small]), inOrder: true);
});

test('can filter incomes by source', function () {
    $market = Income::factory()->forSource(IncomeSource::FarmersMarket)->create();
    $cash = Income::factory()->forSource(IncomeSource::CashSale)->create();

    livewire(ListIncomes::class)
        ->filterTable('source', IncomeSource::FarmersMarket->value)
        ->assertCanSeeTableRecords(collect([$market]))
        ->assertCanNotSeeTableRecords(collect([$cash]));
});
