<?php

use App\Filament\Pages\Tools\InstagramCaptionGenerator;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new InstagramCaptionGenerator;
});

test('data defaults to empty array', function () {
    expect(test()->page->data)->toBeEmpty();
});

test('captions defaults to empty array', function () {
    expect(test()->page->captions)->toBeEmpty();
});

test('generate captions validates required fields', function () {
    test()->page->data = [];

    expect(fn () => test()->page->generateCaptions())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('generate captions produces three variations', function () {
    $category = Category::factory()->create(['name' => 'Bread']);
    $product = Product::factory()->create([
        'name' => 'Sourdough Loaf',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    test()->page->data = [
        'product_id' => $product->id,
        'style' => 'playful',
        'tone' => 'warm',
    ];

    test()->page->generateCaptions();

    expect(test()->page->captions)->toHaveCount(3);
});

test('each caption has text and variation number', function () {
    $category = Category::factory()->create(['name' => 'Cookies']);
    $product = Product::factory()->create([
        'name' => 'Chocolate Chip Cookie',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    test()->page->data = [
        'product_id' => $product->id,
        'style' => 'professional',
        'tone' => 'elegant',
    ];

    test()->page->generateCaptions();

    foreach (test()->page->captions as $i => $caption) {
        expect($caption)->toHaveKeys(['text', 'variation'])
            ->and($caption['variation'])->toBe($i + 1)
            ->and($caption['text'])->toBeString()->not->toBeEmpty();
    }
});

test('generate captions with different styles', function (string $style) {
    $category = Category::factory()->create(['name' => 'Pastry']);
    $product = Product::factory()->create([
        'name' => 'Croissant',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    test()->page->data = [
        'product_id' => $product->id,
        'style' => $style,
        'tone' => 'casual',
    ];

    test()->page->generateCaptions();

    expect(test()->page->captions)->toHaveCount(3);
})->with(['playful', 'professional', 'seasonal', 'storytelling']);

test('generate captions with different tones', function (string $tone) {
    $category = Category::factory()->create(['name' => 'Cake']);
    $product = Product::factory()->create([
        'name' => 'Birthday Cake',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    test()->page->data = [
        'product_id' => $product->id,
        'style' => 'playful',
        'tone' => $tone,
    ];

    test()->page->generateCaptions();

    expect(test()->page->captions)->toHaveCount(3);
})->with(['warm', 'excited', 'casual', 'elegant']);

test('captions include hashtags', function () {
    $category = Category::factory()->create(['name' => 'Bread']);
    $product = Product::factory()->create([
        'name' => 'Baguette',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    test()->page->data = [
        'product_id' => $product->id,
        'style' => 'playful',
        'tone' => 'warm',
    ];

    test()->page->generateCaptions();

    expect(test()->page->captions[0]['text'])->toContain('#');
});

test('generate captions with nonexistent product does nothing', function () {
    test()->page->data = [
        'product_id' => 99999,
        'style' => 'playful',
        'tone' => 'warm',
    ];

    expect(fn () => test()->page->generateCaptions())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});
