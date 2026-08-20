<?php

use App\Enums\Financial\ExpenseCategory;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Models\Financial\Expense;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list expenses in the table', function () {
    $expenses = Expense::factory()->count(3)->create();

    Livewire::test(ListExpenses::class)
        ->assertCanSeeTableRecords($expenses);
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
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Expense::class, [
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
        ->callAction(TestAction::make('edit')->table($expense), data: [
            'description' => 'Updated expense',
            'amount' => $expense->amount->dollars(),
            'category' => $expense->category->value,
            'date' => $expense->date->format('Y-m-d'),
            'business_percentage' => 100,
        ])
        ->assertHasNoFormErrors();

    expect($expense->refresh()->description)->toBe('Updated expense');
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
        ->assertHasFormErrors($errors);
})->with([
    'description is required' => [['description' => null], ['description' => 'required']],
    'amount is required' => [['amount' => null], ['amount' => 'required']],
    'category is required' => [['category' => null], ['category' => 'required']],
    'date is required' => [['date' => null], ['date' => 'required']],
]);

test('can filter expenses by category', function () {
    $ingredients = Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create();
    $packaging = Expense::factory()->forCategory(ExpenseCategory::Packaging)->create();

    Livewire::test(ListExpenses::class)
        ->filterTable('category', ExpenseCategory::Ingredients->value)
        ->assertCanSeeTableRecords(collect([$ingredients]))
        ->assertCanNotSeeTableRecords(collect([$packaging]));
});

test('can search expenses by description', function () {
    $target = Expense::factory()->create(['description' => 'Flour delivery']);
    $other = Expense::factory()->create(['description' => 'Oven repair']);

    Livewire::test(ListExpenses::class)
        ->searchTable('Flour')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort expenses by amount', function () {
    $small = Expense::factory()->create(['amount' => 10]);
    $large = Expense::factory()->create(['amount' => 500]);

    Livewire::test(ListExpenses::class)
        ->sortTable('amount')
        ->assertCanSeeTableRecords(collect([$small, $large]), inOrder: true)
        ->sortTable('amount', 'desc')
        ->assertCanSeeTableRecords(collect([$large, $small]), inOrder: true);
});

test('amount range filter treats input as dollars (not cents)', function () {
    $belowRange = Expense::factory()->create(['amount' => 50]);
    $inRange = Expense::factory()->create(['amount' => 150]);
    $aboveRange = Expense::factory()->create(['amount' => 250]);

    Livewire::test(ListExpenses::class)
        ->filterTable('amount', ['min_amount' => 100, 'max_amount' => 200])
        ->assertCanSeeTableRecords(collect([$inRange]))
        ->assertCanNotSeeTableRecords(collect([$belowRange, $aboveRange]));
});
