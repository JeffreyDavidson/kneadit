<?php

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can list categories in the table', function () {
    $categories = Category::factory()->count(3)->create();

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('can create a category via slide-over', function () {
    Livewire::test(ListCategories::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Pastries',
            'slug' => 'pastries',
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => 'Pastries',
        'slug' => 'pastries',
    ]);
});

test('create category validates name is required', function () {
    Livewire::test(ListCategories::class)
        ->callAction(CreateAction::class, data: [
            'name' => null,
            'slug' => 'test',
        ])
        ->assertHasFormErrors(['name' => 'required']);
});

test('can edit a category via table action', function () {
    $category = Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->callTableAction('edit', $category, data: [
            'name' => 'Updated Category',
            'slug' => $category->slug,
        ])
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('Updated Category');
});

test('can render category table columns', function (string $column) {
    Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'slug', 'is_active']);

test('can search categories by name', function () {
    $bread = Category::factory()->create(['name' => 'Bread']);
    $pastry = Category::factory()->create(['name' => 'Pastries']);

    Livewire::test(ListCategories::class)
        ->searchTable('Bread')
        ->assertCanSeeTableRecords(collect([$bread]))
        ->assertCanNotSeeTableRecords(collect([$pastry]));
});

test('can sort categories by name', function () {
    $alpha = Category::factory()->create(['name' => 'Alpha']);
    $zeta = Category::factory()->create(['name' => 'Zeta']);

    Livewire::test(ListCategories::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('can filter categories by active status', function () {
    $active = Category::factory()->create();
    $inactive = Category::factory()->inactive()->create();

    Livewire::test(ListCategories::class)
        ->filterTable('is_active', 1)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});
