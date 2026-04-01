<?php

use App\Actions\Content\UnpublishBlogPost;
use App\Models\Content\TenantBlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it unpublishes a blog post', function () {
    $post = TenantBlogPost::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);

    resolve(UnpublishBlogPost::class)($post);

    $post->refresh();

    expect($post->is_published)->toBeFalse()
        ->and($post->published_at)->toBeNull();
});
