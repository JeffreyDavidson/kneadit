<?php

use App\Enums\ExpenseCategory;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Models\Expense;
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

test('can create an expense via slide-over', function () {
    Livewire::test(ListExpenses::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Flour delivery',
            'amount' => 150.00,
            'category' => ExpenseCategory::Ingredients->value,
            'date' => '2026-03-26',
            'business_percentage' => 100,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Expense::class, [
        'description' => 'Flour delivery',
    ]);
});

test('can render expense table columns', function (string $column) {
    Expense::factory()->create();

    Livewire::test(ListExpenses::class)
        ->assertCanRenderTableColumn($column);
})->with(['date', 'description', 'category', 'amount']);

test('can edit an expense via table action', function () {
    $expense = Expense::factory()->create();

    Livewire::test(ListExpenses::class)
        ->callTableAction('edit', $expense, data: [
            'description' => 'Updated expense',
            'amount' => $expense->amount,
            'category' => $expense->category->value,
            'date' => $expense->date->format('Y-m-d'),
            'business_percentage' => 100,
        ])
        ->assertHasNoTableActionErrors();

    expect($expense->fresh()->description)->toBe('Updated expense');
});

test('create expense validates required fields', function (array $data, array $errors) {
    Livewire::test(ListExpenses::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Test',
            'amount' => 50,
            'category' => ExpenseCategory::Ingredients->value,
            'date' => '2026-03-26',
            'business_percentage' => 100,
            ...$data,
        ])
        ->assertHasActionErrors($errors);
})->with([
    'description is required' => [['description' => null], ['description' => 'required']],
    'amount is required' => [['amount' => null], ['amount' => 'required']],
    'category is required' => [['category' => null], ['category' => 'required']],
    'date is required' => [['date' => null], ['date' => 'required']],
]);

test('can filter expenses by category', function () {
    $ingredients = Expense::factory()->create(['category' => ExpenseCategory::Ingredients]);
    $packaging = Expense::factory()->create(['category' => ExpenseCategory::Packaging]);

    Livewire::test(ListExpenses::class)
        ->filterTable('category', ExpenseCategory::Ingredients->value)
        ->assertCanSeeTableRecords(collect([$ingredients]))
        ->assertCanNotSeeTableRecords(collect([$packaging]));
});
