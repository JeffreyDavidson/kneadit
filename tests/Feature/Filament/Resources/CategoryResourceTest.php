<?php

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Inventory\Category;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list categories in the table', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('can create a category via slide-over', function () {
    livewire(ListCategories::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Pastries',
            'slug' => 'pastries',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Category::class, [
        'name' => 'Pastries',
        'slug' => 'pastries',
    ]);
});

test('create category validates name is required', function () {
    livewire(ListCategories::class)
        ->callAction(CreateAction::class, data: [
            'name' => null,
            'slug' => 'test',
        ])
        ->assertHasFormErrors(['name' => 'required']);
});

test('can edit a category via table action', function () {
    $category = Category::factory()->create();

    livewire(ListCategories::class)
        ->callAction(TestAction::make('edit')->table($category), data: [
            'name' => 'Updated Category',
            'slug' => $category->slug,
        ])
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('Updated Category');
});

test('can render category table columns', function () {
    Category::factory()->create();

    livewire(ListCategories::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('slug')
        ->assertCanRenderTableColumn('is_active');
});

test('can search categories by name', function () {
    $bread = Category::factory()->create(['name' => 'Bread']);
    $pastry = Category::factory()->create(['name' => 'Pastries']);

    livewire(ListCategories::class)
        ->searchTable('Bread')
        ->assertCanSeeTableRecords(collect([$bread]))
        ->assertCanNotSeeTableRecords(collect([$pastry]));
});

test('can sort categories by name', function () {
    $alpha = Category::factory()->create(['name' => 'Alpha']);
    $zeta = Category::factory()->create(['name' => 'Zeta']);

    livewire(ListCategories::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('can filter categories by active status', function () {
    $active = Category::factory()->create();
    $inactive = Category::factory()->inactive()->create();

    livewire(ListCategories::class)
        ->filterTable('is_active', 1)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});
