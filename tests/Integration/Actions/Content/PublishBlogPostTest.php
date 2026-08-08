<?php

use App\Actions\Content\PublishBlogPost;
use App\Models\Content\TenantBlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it publishes a blog post', function () {
    $post = TenantBlogPost::factory()->create([
        'is_published' => false,
        'published_at' => null,
    ]);

    resolve(PublishBlogPost::class)($post);

    $post->refresh();

    expect($post->is_published)->toBeTrue()
        ->and($post->published_at)->not->toBeNull();
});
