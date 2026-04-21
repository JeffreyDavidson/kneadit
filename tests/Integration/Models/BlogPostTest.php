<?php

use App\Models\Content\BlogPost;
use App\Models\Staff\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    DB::connection('central')->setPdo(
        DB::connection('sqlite')->getPdo(),
    );
    User::factory()->owner()->create();
});

test('slug is auto generated from title', function () {
    $post = BlogPost::factory()->create([
        'title' => 'My Awesome Blog Post',
        'slug' => null,
        'body' => 'Some content here',
    ]);

    expect($post->slug)->toBe('my-awesome-blog-post');
});

test('slug is updated when title changes', function () {
    $post = BlogPost::factory()->create([
        'title' => 'Original Title',
        'slug' => null,
        'body' => 'Content',
    ]);

    $post->update(['title' => 'Updated Title']);

    expect($post->fresh()->slug)->toBe('updated-title');
});

test('duplicate slugs are made unique', function () {
    BlogPost::factory()->create(['title' => 'Same Title', 'slug' => null, 'body' => 'First']);
    $post2 = BlogPost::factory()->create(['title' => 'Same Title', 'slug' => null, 'body' => 'Second']);

    expect($post2->slug)->toBe('same-title-2');
});

test('published at can be set', function () {
    $post = BlogPost::factory()->published()->create();

    expect($post->is_published)->toBeTrue()->and($post->published_at)->not->toBeNull();
});

test('is published is cast to boolean', function () {
    $post = BlogPost::factory()->published()->create();

    expect($post->fresh()->is_published)->toBeBool();
});

test('published at is cast to datetime', function () {
    $post = BlogPost::factory()->published()->create();

    expect($post->fresh()->published_at)->toBeInstanceOf(Carbon::class);
});

test('url accessor returns route to blog show', function () {
    $post = BlogPost::factory()->published()->create([
        'slug' => 'test-blog-post',
    ]);

    expect($post->url)->toContain('test-blog-post');
});
