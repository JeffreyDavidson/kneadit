<?php

use App\Actions\Content\CopyPrivateImagesToPublicDisk;
use App\Models\Content\SocialPost;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Storage::fake('local');
    Storage::fake('public');
});

test('a dry run reports private images without copying them', function () {
    Storage::disk('local')->put('products/loaf.jpg', 'loaf');
    ProductImage::factory()->for(Product::factory())->create(['path' => 'products/loaf.jpg']);

    $result = app(CopyPrivateImagesToPublicDisk::class)(apply: false);

    expect($result['copied'])->toBe(['products/loaf.jpg']);
    Storage::disk('public')->assertMissing('products/loaf.jpg');
});

test('applying copies product and social post images to the public disk and keeps the source', function () {
    Storage::disk('local')->put('products/loaf.jpg', 'loaf');
    Storage::disk('local')->put('social-posts/post.jpg', 'post');
    ProductImage::factory()->for(Product::factory())->create(['path' => 'products/loaf.jpg']);
    SocialPost::factory()->create(['image_path' => 'social-posts/post.jpg']);

    $result = app(CopyPrivateImagesToPublicDisk::class)(apply: true);

    expect($result['copied'])->toEqualCanonicalizing(['products/loaf.jpg', 'social-posts/post.jpg']);
    Storage::disk('public')->assertExists('products/loaf.jpg');
    Storage::disk('public')->assertExists('social-posts/post.jpg');
    Storage::disk('local')->assertExists('products/loaf.jpg');
    Storage::disk('local')->assertExists('social-posts/post.jpg');
});

test('images already on the public disk are skipped and unknown paths are reported as missing', function () {
    Storage::disk('public')->put('tenants/bakery/boule.jpg', 'boule');
    Product::factory()->create(['image' => 'tenants/bakery/boule.jpg']);
    SocialPost::factory()->create(['image_path' => 'social-posts/gone.jpg']);

    $result = app(CopyPrivateImagesToPublicDisk::class)(apply: true);

    expect($result['copied'])->toBeEmpty()
        ->and($result['missing'])->toBe(['social-posts/gone.jpg']);
});
