<?php

use App\Models\Content\TenantBlogPost;
use App\Routing\Resolvers\PublishedTenantBlogPostResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns a published tenant blog post by slug', function () {
    $post = TenantBlogPost::factory()->create([
        'slug' => 'fresh-loaves',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $resolved = (new PublishedTenantBlogPostResolver)('fresh-loaves');

    expect($resolved)->toBeInstanceOf(TenantBlogPost::class)
        ->and($resolved->id)->toBe($post->id);
});

test('throws ModelNotFoundException for an unpublished post', function () {
    TenantBlogPost::factory()->draft()->create(['slug' => 'work-in-progress']);

    (new PublishedTenantBlogPostResolver)('work-in-progress');
})->throws(ModelNotFoundException::class);

test('throws ModelNotFoundException for a missing slug', function () {
    (new PublishedTenantBlogPostResolver)('does-not-exist');
})->throws(ModelNotFoundException::class);
