<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SocialPost;
use Illuminate\Support\Carbon;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('social post model exists', function () {
    expect(class_exists(SocialPost::class))->toBeTrue();
});

test('social post can be created', function () {
    SocialPost::create([
        'platform' => 'instagram',
        'caption' => 'Check out our new sourdough! 🍞',
        'status' => 'draft',
    ]);

    $this->assertDatabaseHas('social_posts', [
        'platform' => 'instagram',
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
    $post = SocialPost::create([
        'platform' => 'facebook',
        'caption' => 'Test post',
        'status' => 'draft',
    ]);

    expect($post->status)->toBe('draft');
});

test('statuses are defined', function () {
    expect(SocialPost::STATUSES)->toHaveKey('draft');
    expect(SocialPost::STATUSES)->toHaveKey('scheduled');
    expect(SocialPost::STATUSES)->toHaveKey('posted');
});

test('scheduled post has scheduled for date', function () {
    $scheduledDate = now()->addDays(3);

    $post = SocialPost::create([
        'platform' => 'instagram',
        'caption' => 'Scheduled post',
        'status' => 'scheduled',
        'scheduled_for' => $scheduledDate,
    ]);

    expect($post->scheduled_for)->not->toBeNull();
    expect($post->scheduled_for)->toBeInstanceOf(Carbon::class);
});

test('social post belongs to product', function () {
    $category = Category::create(['name' => 'Bread', 'slug' => 'bread', 'sort_order' => 0]);
    $product = Product::create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 8.99,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $post = SocialPost::create([
        'platform' => 'instagram',
        'caption' => 'Our famous sourdough',
        'product_id' => $product->id,
        'status' => 'draft',
    ]);

    expect($post->product->id)->toBe($product->id);
});
