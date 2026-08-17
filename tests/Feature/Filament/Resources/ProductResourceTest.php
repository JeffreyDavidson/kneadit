<?php

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    test()->category = Category::factory()->create();
});

test('can render products list page', function () {
    Livewire::test(ListProducts::class)
        ->assertOk();
});

test('can list products in the table', function () {
    $products = Product::factory()->recycle(test()->category)->count(3)->create();

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

test('can search products by name', function () {
    $sourdough = Product::factory()->create(['name' => 'Sourdough Loaf']);
    $baguette = Product::factory()->create(['name' => 'Baguette']);

    Livewire::test(ListProducts::class)
        ->searchTable('Sourdough')
        ->assertCanSeeTableRecords(collect([$sourdough]))
        ->assertCanNotSeeTableRecords(collect([$baguette]));
});

test('can render product table columns', function (string $column) {
    Product::factory()->recycle(test()->category)->create();

    Livewire::test(ListProducts::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'category.name', 'price', 'is_active', 'is_featured']);

test('can create a product via slide-over', function () {
    $category = Category::factory()->create();

    Livewire::test(ListProducts::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Ciabatta Roll',
            'slug' => 'ciabatta-roll',
            'price' => 4.50,
            'category_id' => $category->id,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Product::class, [
        'name' => 'Ciabatta Roll',
        'slug' => 'ciabatta-roll',
    ]);
});

test('create product validates required fields', function (array $data, array $errors) {
    $category = Category::factory()->create();

    Livewire::test(ListProducts::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Test',
            'slug' => 'test',
            'price' => 5.00,
            'category_id' => $category->id,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'slug is required' => [['slug' => null], ['slug' => 'required']],
    'price is required' => [['price' => null], ['price' => 'required']],
    'category is required' => [['category_id' => null], ['category_id' => 'required']],
]);

test('can filter products by active status', function () {
    $active = Product::factory()->active()->create();
    $inactive = Product::factory()->inactive()->create();

    Livewire::test(ListProducts::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});

test('can sort products by name', function () {
    $apple = Product::factory()->create(['name' => 'Apple Tart']);
    $zucchini = Product::factory()->create(['name' => 'Zucchini Bread']);

    Livewire::test(ListProducts::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$apple, $zucchini]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zucchini, $apple]), inOrder: true);
});

test('can edit a product via table action', function () {
    $product = Product::factory()->recycle(test()->category)->create();

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('edit')->table($product), data: [
            'name' => 'Updated Bread',
            'slug' => $product->slug,
            'price' => $product->price?->dollars(),
            'category_id' => $product->category_id,
        ])
        ->assertHasNoFormErrors();

    expect($product->refresh()->name)->toBe('Updated Bread');
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Products\ProductResource::getGloballySearchableAttributes())
        ->toBe(['name', 'description', 'category.name']);
});

test('resource returns global search result title', function () {
    $product = Product::factory()->recycle(test()->category)->create(['name' => 'Ciabatta']);

    expect(App\Filament\Resources\Products\ProductResource::getGlobalSearchResultTitle($product))
        ->toBe('Ciabatta');
});

test('resource returns global search result details', function () {
    $product = Product::factory()->recycle(test()->category)->create(['price' => 5.50]);

    $details = App\Filament\Resources\Products\ProductResource::getGlobalSearchResultDetails($product);

    expect($details)
        ->toHaveKeys(['Category', 'Price']);
});

test('global search eloquent query eager loads category', function () {
    $query = App\Filament\Resources\Products\ProductResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('category');
});

test('owner can bulk-delete selected products via the AuthorizedDeleteBulkAction', function () {
    $kept = Product::factory()->recycle(test()->category)->create();
    $doomed = Product::factory()->recycle(test()->category)->count(2)->create();

    Livewire::test(ListProducts::class)
        ->selectTableRecords($doomed)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Product::query()->count())->toBe(1)
        ->and(Product::query()->find($kept->id))->not->toBeNull()
        ->and(Product::query()->find($doomed->firstOrFail()->id))->toBeNull();
});
