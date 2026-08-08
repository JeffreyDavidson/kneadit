<?php

use App\Models\Content\BlogPost;
use App\Routing\Resolvers\PublishedBlogPostResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    DB::connection('central')->setPdo(DB::connection('sqlite')->getPdo());
});

test('returns a published blog post by slug', function () {
    $post = BlogPost::factory()->published()->create(['slug' => 'sourdough-tips']);

    $resolved = (new PublishedBlogPostResolver)('sourdough-tips');

    expect($resolved)->toBeInstanceOf(BlogPost::class)
        ->and($resolved->id)->toBe($post->id);
});

test('throws ModelNotFoundException for an unpublished post', function () {
    BlogPost::factory()->draft()->create(['slug' => 'work-in-progress']);

    (new PublishedBlogPostResolver)('work-in-progress');
})->throws(ModelNotFoundException::class);

test('throws ModelNotFoundException for a missing slug', function () {
    (new PublishedBlogPostResolver)('does-not-exist');
})->throws(ModelNotFoundException::class);
