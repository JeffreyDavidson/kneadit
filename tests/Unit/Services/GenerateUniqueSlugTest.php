<?php

use App\Models\TenantBlogPost;
use App\Services\GenerateUniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates a slug from a title', function () {
    $slug = (new GenerateUniqueSlug)(TenantBlogPost::class, 'My Great Post');

    expect($slug)->toBe('my-great-post');
});

test('it appends number when slug already exists', function () {
    TenantBlogPost::factory()->create(['slug' => 'my-great-post']);

    $slug = (new GenerateUniqueSlug)(TenantBlogPost::class, 'My Great Post');

    expect($slug)->toBe('my-great-post-2');
});
