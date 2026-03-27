<?php

use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can create a supplier via slide-over', function () {
    Livewire::test(ListSuppliers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Flour Mill Co.',
            'contact_name' => 'John Miller',
            'email' => 'john@flourmill.com',
            'phone' => '555-0100',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Supplier::class, [
        'name' => 'Flour Mill Co.',
    ]);
});

test('can edit a supplier via table action', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(ListSuppliers::class)
        ->callTableAction('edit', $supplier, data: [
            'name' => 'Updated Supplier',
        ])
        ->assertHasNoTableActionErrors();

    expect($supplier->fresh()->name)->toBe('Updated Supplier');
});
