<?php

use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Inventory\Supplier;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list suppliers in the table', function () {
    $suppliers = Supplier::factory()->count(3)->create();

    livewire(ListSuppliers::class)
        ->assertCanSeeTableRecords($suppliers);
});

test('can create a supplier via slide-over', function () {
    livewire(ListSuppliers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Flour Mill Co.',
            'contact_name' => 'John Miller',
            'email' => 'john@flourmill.com',
            'phone' => '555-0100',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Supplier::class, [
        'name' => 'Flour Mill Co.',
    ]);
});

test('can edit a supplier via table action', function () {
    $supplier = Supplier::factory()->create();

    livewire(ListSuppliers::class)
        ->callAction(TestAction::make('edit')->table($supplier), data: [
            'name' => 'Updated Supplier',
        ])
        ->assertHasNoFormErrors();

    expect($supplier->fresh()->name)->toBe('Updated Supplier');
});

test('can search suppliers by name', function () {
    $target = Supplier::factory()->create(['name' => 'Flour Mill Co.']);
    $other = Supplier::factory()->create(['name' => 'Sugar Supply Inc.']);

    livewire(ListSuppliers::class)
        ->searchTable('Flour')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('create supplier validates name is required', function () {
    livewire(ListSuppliers::class)
        ->callAction(CreateAction::class, data: [
            'name' => null,
        ])
        ->assertHasFormErrors(['name' => 'required']);
});

test('can render supplier table columns', function () {
    Supplier::factory()->create();

    livewire(ListSuppliers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('contact_name')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('phone');
});

test('can sort suppliers by name', function () {
    $alpha = Supplier::factory()->create(['name' => 'Alpha Mills']);
    $zeta = Supplier::factory()->create(['name' => 'Zeta Supply']);

    livewire(ListSuppliers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('can filter suppliers by active status', function () {
    $active = Supplier::factory()->active()->create();
    $inactive = Supplier::factory()->inactive()->create();

    livewire(ListSuppliers::class)
        ->filterTable('is_active', 1)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});

test('owner can bulk-delete selected suppliers via the AuthorizedDeleteBulkAction', function () {
    $kept = Supplier::factory()->create();
    $doomed = Supplier::factory()->count(2)->create();

    livewire(ListSuppliers::class)
        ->selectTableRecords($doomed)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Supplier::query()->count())->toBe(1)
        ->and(Supplier::query()->find($kept->id))->not->toBeNull()
        ->and(Supplier::query()->find($doomed->first()->id))->toBeNull();
});
