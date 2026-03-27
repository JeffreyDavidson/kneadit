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
