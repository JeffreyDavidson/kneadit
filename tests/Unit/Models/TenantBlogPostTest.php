<?php

use App\Models\Content\TenantBlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it can be created with factory', function () {
    $post = TenantBlogPost::factory()->create();

    expect($post)->toBeInstanceOf(TenantBlogPost::class)
        ->and($post->title)->toBeString();
});

test('published scope filters correctly', function () {
    TenantBlogPost::factory()->published()->create();
    TenantBlogPost::factory()->create(); // draft

    expect(TenantBlogPost::query()->published()->count())->toBe(1);
});
