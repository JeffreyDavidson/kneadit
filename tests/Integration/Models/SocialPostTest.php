<?php

use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Models\Product;
use App\Models\SocialPost;
use Illuminate\Support\Carbon;

beforeEach(fn () => setUpTenantTest());

test('social post model exists', function () {
    expect(class_exists(SocialPost::class))->toBeTrue();
});

test('social post can be created', function () {
    SocialPost::factory()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Check out our new sourdough! 🍞',
    ]);

    $this->assertDatabaseHas('social_posts', [
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Check out our new sourdough! 🍞',
    ]);
});

test('platform max lengths are defined', function () {
    expect(SocialPost::PLATFORM_MAX_CHARS)->toMatchArray(['instagram' => 2200, 'facebook' => 63206, 'tiktok' => 4000]);
});

test('platforms are defined', function () {
    expect(SocialPost::PLATFORMS)->toHaveKeys(['instagram', 'facebook', 'tiktok']);
});

test('status defaults to draft', function () {
    $post = SocialPost::factory()->create([
        'platform' => SocialPlatform::Facebook,
        'caption' => 'Test post',
    ]);

    expect($post->status)->toBe(SocialPostStatus::Draft);
});

test('status enum has expected cases', function () {
    expect(SocialPostStatus::cases())->toHaveCount(3);
});

test('scheduled post has scheduled for date', function () {
    $scheduledDate = now()->addDays(3);

    $post = SocialPost::factory()->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Scheduled post',
        'status' => SocialPostStatus::Scheduled,
        'scheduled_for' => $scheduledDate,
    ]);

    expect($post->scheduled_for)->not->toBeNull()->toBeInstanceOf(Carbon::class);
});

test('social post belongs to product', function () {
    $product = Product::factory()->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 8.99,
    ]);

    $post = SocialPost::factory()->for($product)->create([
        'platform' => SocialPlatform::Instagram,
        'caption' => 'Our famous sourdough',
    ]);

    expect($post->product->id)->toBe($product->id);
});
