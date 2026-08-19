<?php

use App\Models\Content\TenantBlogPost;
use Illuminate\Support\Collection;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('blog feed returns RSS-typed response with published posts', function () {
    TenantBlogPost::factory()->published()->create(['title' => 'Sourdough Tips']);
    TenantBlogPost::factory()->draft()->create(['title' => 'Work In Progress']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.feed', [], false));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('Sourdough Tips')
        ->assertDontSee('Work In Progress');
});

test('blog feed limits results to 20 most recent posts', function () {
    TenantBlogPost::factory()->published()->count(25)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.feed', [], false));

    $response->assertOk()
        ->assertViewHas('posts', fn (Collection $posts): bool => $posts->count() === 20);
});
