<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\View\Components\Home\Hero;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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

test('uses configured hero tagline and CTA text', function () {
    settings([
        'hero_tagline' => 'Freshly baked daily',
        'hero_primary_cta_text' => 'Place Your Order',
        'hero_secondary_cta_text' => 'See Our Menu',
    ]);

    $component = new Hero;

    expect($component->heroTagline)->toBe('Freshly baked daily')
        ->and($component->primaryCtaText)->toBe('Place Your Order')
        ->and($component->secondaryCtaText)->toBe('See Our Menu');
});

test('hero CTA text falls back to defaults when not set', function () {
    $component = new Hero;

    expect($component->primaryCtaText)->toBe('Order Now')
        ->and($component->secondaryCtaText)->toBe('Browse Menu')
        ->and($component->heroTagline)->toBeNull();
});
