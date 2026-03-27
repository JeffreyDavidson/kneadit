<?php

use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\SocialPost;
use Illuminate\Support\Carbon;

beforeEach(fn () => setUpTenantTest());

test('social post model exists', function () {
    expect(class_exists(SocialPost::class))->toBeTrue();
});

test('social post can be created', function () {
    SocialPost::query()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Check out our new sourdough! 🍞',
        'status' => SocialPostStatus::Draft,
    ]);

    $this->assertDatabaseHas('social_posts', [
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Check out our new sourdough! 🍞',
    ]);
});

test('platform max lengths are defined', function () {
    expect(SocialPost::PLATFORM_MAX_CHARS['instagram'])->toBe(2200);
    expect(SocialPost::PLATFORM_MAX_CHARS['facebook'])->toBe(63206);
    expect(SocialPost::PLATFORM_MAX_CHARS['tiktok'])->toBe(4000);
});

test('platforms are defined', function () {
    expect(SocialPost::PLATFORMS)->toHaveKey('instagram');
    expect(SocialPost::PLATFORMS)->toHaveKey('facebook');
    expect(SocialPost::PLATFORMS)->toHaveKey('tiktok');
});

test('status defaults to draft', function () {
    $post = SocialPost::query()->create([
        'platform' => SocialPlatform::Facebook,
        'caption' => 'Test post',
        'status' => SocialPostStatus::Draft,
    ]);

    expect($post->status)->toBe(SocialPostStatus::Draft);
});

test('status enum has expected cases', function () {
    expect(SocialPostStatus::cases())->toHaveCount(3);
});

test('scheduled post has scheduled for date', function () {
    $scheduledDate = now()->addDays(3);

    $post = SocialPost::query()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Scheduled post',
        'status' => SocialPostStatus::Scheduled,
        'scheduled_for' => $scheduledDate,
    ]);

    expect($post->scheduled_for)->not->toBeNull();
    expect($post->scheduled_for)->toBeInstanceOf(Carbon::class);
});

test('social post belongs to product', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread', 'sort_order' => 0]);
    $product = Product::query()->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 8.99,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $post = SocialPost::query()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Our famous sourdough',
        'product_id' => $product->id,
        'status' => SocialPostStatus::Draft,
    ]);

    expect($post->product->id)->toBe($product->id);
});
