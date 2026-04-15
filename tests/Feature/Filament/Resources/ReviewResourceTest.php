<?php

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
    test()->product = Product::factory()->create();
});

test('can list reviews in the table', function () {
    $reviews = Review::factory()->recycle(test()->product)->count(3)->create();

    Livewire::test(ListReviews::class)
        ->assertCanSeeTableRecords($reviews);
});

test('can create a review via slide-over', function () {
    Livewire::test(ListReviews::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Happy Customer',
            'customer_email' => 'happy@example.com',
            'rating' => 5,
            'comment' => 'Best bread ever!',
            'is_approved' => true,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Review::class, [
        'customer_name' => 'Happy Customer',
        'rating' => 5,
    ]);
});

test('can render review table columns', function (string $column) {
    Review::factory()->recycle(test()->product)->create();

    Livewire::test(ListReviews::class)
        ->assertCanRenderTableColumn($column);
})->with(['customer_name', 'product.name', 'rating', 'is_approved', 'is_featured']);

test('can edit a review via table action', function () {
    $review = Review::factory()->recycle(test()->product)->create();

    Livewire::test(ListReviews::class)
        ->callAction(TestAction::make('edit')->table($review), data: [
            'customer_name' => 'Updated Reviewer',
            'customer_email' => $review->customer_email,
            'rating' => $review->rating,
            'is_approved' => true,
        ])
        ->assertHasNoFormErrors();

    expect($review->fresh()->customer_name)->toBe('Updated Reviewer');
});

test('can filter reviews by approval status', function () {
    $approved = Review::factory()->approved()->create();
    $pending = Review::factory()->recycle(test()->product)->create();

    Livewire::test(ListReviews::class)
        ->filterTable('is_approved', 1)
        ->assertCanSeeTableRecords(collect([$approved]))
        ->assertCanNotSeeTableRecords(collect([$pending]));
});

test('create review validates required fields', function (array $data, array $errors) {
    Livewire::test(ListReviews::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'rating' => 5,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'customer name is required' => [['customer_name' => null], ['customer_name' => 'required']],
    'customer email is required' => [['customer_email' => null], ['customer_email' => 'required']],
    'rating is required' => [['rating' => null], ['rating' => 'required']],
]);

test('can search reviews by customer name', function () {
    $target = Review::factory()->create(['customer_name' => 'Alice Baker']);
    $other = Review::factory()->create(['customer_name' => 'Bob Smith']);

    Livewire::test(ListReviews::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort reviews by customer name', function () {
    $alice = Review::factory()->create(['customer_name' => 'Alice']);
    $zach = Review::factory()->create(['customer_name' => 'Zach']);

    Livewire::test(ListReviews::class)
        ->sortTable('customer_name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('customer_name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Reviews\ReviewResource::getGloballySearchableAttributes())
        ->toBe(['customer_name', 'customer_email']);
});

test('resource returns global search result title', function () {
    $review = Review::factory()->recycle(test()->product)->create(['customer_name' => 'Alice Baker']);

    expect(App\Filament\Resources\Reviews\ReviewResource::getGlobalSearchResultTitle($review))
        ->toBe('Review by Alice Baker');
});

test('resource returns global search result details', function () {
    $review = Review::factory()->recycle(test()->product)->create(['rating' => 4]);

    $details = App\Filament\Resources\Reviews\ReviewResource::getGlobalSearchResultDetails($review);

    expect($details)
        ->toHaveKey('Rating')
        ->toHaveKey('Product');
});

test('global search eloquent query eager loads product', function () {
    $query = App\Filament\Resources\Reviews\ReviewResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('product');
});
