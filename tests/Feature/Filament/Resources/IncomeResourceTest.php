<?php

use App\Enums\IncomeSource;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Models\Income;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can create an income via slide-over', function () {
    Livewire::test(ListIncomes::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Farmers market sales',
            'amount' => 350.00,
            'source' => IncomeSource::FarmersMarket->value,
            'date' => '2026-03-26',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Income::class, [
        'description' => 'Farmers market sales',
    ]);
});

test('can render income table columns', function (string $column) {
    Income::factory()->create();

    Livewire::test(ListIncomes::class)
        ->assertCanRenderTableColumn($column);
})->with(['date', 'description', 'source', 'amount']);

test('can edit an income via table action', function () {
    $income = Income::factory()->create();

    Livewire::test(ListIncomes::class)
        ->callTableAction('edit', $income, data: [
            'description' => 'Updated income',
            'amount' => $income->amount,
            'source' => $income->source->value,
        ])
        ->assertHasNoTableActionErrors();

    expect($income->fresh()->description)->toBe('Updated income');
});

test('create income validates required fields', function (array $data, array $errors) {
    Livewire::test(ListIncomes::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Test',
            'amount' => 100,
            'source' => IncomeSource::FarmersMarket->value,
            ...$data,
        ])
        ->assertHasActionErrors($errors);
})->with([
    'description is required' => [['description' => null], ['description' => 'required']],
    'amount is required' => [['amount' => null], ['amount' => 'required']],
    'source is required' => [['source' => null], ['source' => 'required']],
]);
