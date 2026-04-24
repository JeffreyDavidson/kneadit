<?php

use App\Enums\Content\CaptionStyle;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Services\Content\CaptionGeneratorService;

beforeEach(fn () => setUpTenantTest());

test('generates requested number of captions', function () {
    $product = Product::factory()
        ->for(Category::factory()->create(['name' => 'Breads', 'slug' => 'breads']))
        ->create(['name' => 'Sourdough Loaf']);

    $captions = resolve(CaptionGeneratorService::class)
        ->generate($product, CaptionStyle::Playful, 'warm', 3);

    expect($captions)->toHaveCount(3);
});

test('each caption has text and variation keys', function () {
    $product = Product::factory()
        ->for(Category::factory()->create(['name' => 'Cakes', 'slug' => 'cakes']))
        ->create(['name' => 'Chocolate Cake']);

    $captions = resolve(CaptionGeneratorService::class)
        ->generate($product, CaptionStyle::Professional, 'elegant', 1);

    expect($captions[0])
        ->toHaveKeys(['text', 'variation'])
        ->and($captions[0]['text'])->toBeString()->not->toBeEmpty()
        ->and($captions[0]['variation'])->toBeInt();
});

test('generates captions for all styles', function (CaptionStyle $style) {
    $product = Product::factory()
        ->for(Category::factory()->create(['name' => 'Cookies', 'slug' => 'cookies']))
        ->create(['name' => 'Oatmeal Cookie']);

    $captions = resolve(CaptionGeneratorService::class)
        ->generate($product, $style, 'casual', 1);

    expect($captions)->toHaveCount(1);
})->with([
    CaptionStyle::Playful,
    CaptionStyle::Professional,
    CaptionStyle::Seasonal,
    CaptionStyle::Storytelling,
]);
