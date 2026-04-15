<?php

use App\Models\Content\TenantBlogPost;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('blog index shows published tenant posts', function () {
    TenantBlogPost::factory()->published()->create(['title' => 'My First Recipe']);
    TenantBlogPost::factory()->create(['title' => 'Draft Post']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog', [], false));

    $response->assertOk()
        ->assertViewIs('storefront.blog.index')
        ->assertSee('My First Recipe')
        ->assertDontSee('Draft Post');
});
