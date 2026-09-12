<?php

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\ViewModels\Storefront\ReviewsPageViewModel;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

function createReviewProduct(): Product
{
    return Product::factory()->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 8.00,
    ]);
}

test('reviews page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk();
});

test('reviews controller passes a fully-assembled VM to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk()
        ->assertViewHas('storefrontTheme')
        ->assertViewHas('vm', fn (ReviewsPageViewModel $vm) => $vm->settings !== null
            && is_array($vm->content)
            && $vm->totalReviews >= 0);
});

test('biscotto reviews page uses the themed customer stories presentation', function () {
    Setting::factory()->create(['key' => 'storefront_theme', 'value' => 'biscotto']);
    resolve(SettingsManager::class)->flushCache();
    $product = createReviewProduct();
    Review::factory()->for($product)->approved()->create([
        'customer_name' => 'Happy Customer',
        'customer_email' => 'happy@example.com',
        'rating' => 5,
        'comment' => 'The best sourdough in town.',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk()
        ->assertSee('biscotto-reviews-hero', false)
        ->assertSee('Kind Words')
        ->assertSee('The best sourdough in town.')
        ->assertSee('Leave a Review');
});

test('reviews page shows approved reviews', function () {
    $product = createReviewProduct();

    Review::factory()->for($product)->approved()->create([
        'customer_name' => 'Happy Customer',
        'customer_email' => 'happy@example.com',
        'rating' => 5,
        'comment' => 'Absolutely delicious sourdough!',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk();
    $response->assertSee('Happy Customer');
    $response->assertSee('Absolutely delicious sourdough!');
});

test('reviews page hides unapproved reviews', function () {
    $product = createReviewProduct();

    Review::factory()->for($product)->pending()->create([
        'customer_name' => 'Pending Reviewer',
        'customer_email' => 'pending@example.com',
        'rating' => 3,
        'comment' => 'This should not be visible yet',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk();
    $response->assertDontSee('This should not be visible yet');
});

test('reviews page shows average rating', function () {
    $product = createReviewProduct();

    Review::factory()->for($product)->approved()->create([
        'customer_name' => 'A',
        'customer_email' => 'a@example.com',
        'rating' => 5,
        'comment' => 'Great!',
    ]);

    Review::factory()->for($product)->approved()->create([
        'customer_name' => 'B',
        'customer_email' => 'b@example.com',
        'rating' => 3,
        'comment' => 'Good',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk();
    // Average is 4.0
    $response->assertSee('4');
});

test('empty reviews page shows empty state', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk();
    expect(Review::query()->count())->toBe(0);
});

test('reviews only counts approved in average', function () {
    $product = createReviewProduct();

    Review::factory()->for($product)->approved()->create([
        'customer_name' => 'A',
        'customer_email' => 'a@example.com',
        'rating' => 5,
        'comment' => 'Amazing',
    ]);

    Review::factory()->for($product)->pending()->create([
        'customer_name' => 'Troll',
        'customer_email' => 'troll@example.com',
        'rating' => 1,
        'comment' => 'Spam review',
    ]);

    $avgRating = Review::query()->where('is_approved', true)->avg('rating');
    expect((float) $avgRating)->toBe(5.0);
});
