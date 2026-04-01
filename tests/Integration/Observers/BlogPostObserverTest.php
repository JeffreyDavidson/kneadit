<?php

use App\Models\Content\BlogPost;

beforeEach(fn () => setUpCentralTest());

test('slug is auto-generated from title on create', function () {
    $post = BlogPost::factory()->create(['title' => 'My First Blog Post', 'slug' => '']);

    expect($post->slug)->toBe('my-first-blog-post');
});

test('slug updates when title changes', function () {
    $post = BlogPost::factory()->create(['title' => 'Original Title', 'slug' => 'original-title']);

    $post->update(['title' => 'Updated Title']);

    expect($post->fresh()->slug)->toBe('updated-title');
});
