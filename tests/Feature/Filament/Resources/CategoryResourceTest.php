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

test('can create a category via slide-over', function () {
    Livewire::test(ListCategories::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Pastries',
            'slug' => 'pastries',
        ])
        ->assertHasNoActionErrors();

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
        ->assertHasActionErrors(['name' => 'required']);
});

test('can edit a category via table action', function () {
    $category = Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->callTableAction('edit', $category, data: [
            'name' => 'Updated Category',
            'slug' => $category->slug,
        ])
        ->assertHasNoTableActionErrors();

    expect($category->fresh()->name)->toBe('Updated Category');
});

test('can render category table columns', function (string $column) {
    Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'slug', 'is_active']);
