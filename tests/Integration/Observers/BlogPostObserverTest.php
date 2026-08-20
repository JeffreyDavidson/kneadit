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

    expect($post->refresh()->slug)->toBe('updated-title');
});

test('slug is unique when updating title to match existing post', function () {
    BlogPost::factory()->create(['title' => 'Taken Title', 'slug' => 'taken-title']);
    $post = BlogPost::factory()->create(['title' => 'Original Title', 'slug' => 'original-title']);

    $post->update(['title' => 'Taken Title']);

    expect($post->refresh()->slug)->toBe('taken-title-2');
});

test('slug does not change when updating non-title fields', function () {
    $post = BlogPost::factory()->create(['title' => 'My Post', 'slug' => 'my-post']);

    $post->update(['excerpt' => 'Updated excerpt']);

    expect($post->refresh()->slug)->toBe('my-post');
});

test('slug keeps same value when title update produces same slug', function () {
    $post = BlogPost::factory()->create(['title' => 'My Post', 'slug' => 'my-post']);

    $post->update(['title' => 'My Post Updated']);

    expect($post->refresh()->slug)->toBe('my-post-updated');
});
