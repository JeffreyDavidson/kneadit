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
