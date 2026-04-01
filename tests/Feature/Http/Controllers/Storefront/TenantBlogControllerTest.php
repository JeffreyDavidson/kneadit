<?php

use App\Models\Content\TenantBlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('blog index shows published tenant posts', function () {
    TenantBlogPost::factory()->published()->create(['title' => 'My First Recipe']);
    TenantBlogPost::factory()->create(['title' => 'Draft Post']);

    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog'));

    $response->assertOk()
        ->assertViewIs('storefront.blog.index')
        ->assertSee('My First Recipe')
        ->assertDontSee('Draft Post');
});
