<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\View\Components\Home\Hero;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads store name and customer metrics', function () {
    settings(['store_name' => 'Sweet Dreams Bakery']);
    Customer::factory()->count(5)->create();
    Review::factory()->approved()->create(['rating' => 5]);

    $component = new Hero;

    expect($component->storeName)->toBe('Sweet Dreams Bakery')
        ->and($component->customerCount)->toBe(5)
        ->and($component->avgRating)->toBe(5.0)
        ->and($component->topReview)->not->toBeNull();
});
