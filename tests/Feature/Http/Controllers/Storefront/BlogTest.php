<?php

use App\Models\Content\BlogPost;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpCentralTest();
});

test('blog index page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog', [], false));

    $response->assertOk();
});

test('blog shows only published posts', function () {
    BlogPost::factory()->published()->create([
        'title' => 'Published Post',
        'body' => 'Content here',
        'published_at' => now()->subDay(),
    ]);

    BlogPost::factory()->create([
        'title' => 'Draft Post',
        'body' => 'Draft content',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog', [], false));

    $response->assertOk();
    $response->assertSee('Published Post');
    $response->assertDontSee('Draft Post');
});

test('blog hides draft posts', function () {
    BlogPost::factory()->create([
        'title' => 'My Draft',
        'body' => 'Not ready yet',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog', [], false));

    $response->assertOk();
    $response->assertDontSee('My Draft');
});

test('blog paginates at six per page', function () {
    for ($i = 1; $i <= 8; $i++) {
        BlogPost::factory()->published()->create([
            'title' => "Post Number $i",
            'body' => "Body $i",
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog', [], false));

    $response->assertOk();
    $response->assertSee('Post Number 1');
    $response->assertSee('Post Number 6');
    $response->assertDontSee('Post Number 7');
});

test('individual blog post loads by slug', function () {
    $post = BlogPost::factory()->published()->create([
        'title' => 'Sourdough Tips',
        'slug' => 'sourdough-tips',
        'body' => 'Here are some tips for sourdough.',
        'published_at' => now()->subDay(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.show', $post, false));

    $response->assertOk();
    $response->assertSee('Sourdough Tips');
});

test('returns 404 for nonexistent slug', function () {
    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.show', 'does-not-exist', false));

    $response->assertNotFound();
});

test('returns 404 for draft post slug', function () {
    $post = BlogPost::factory()->create([
        'title' => 'Secret Draft',
        'slug' => 'secret-draft',
        'body' => 'Hidden content',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.show', $post, false));

    $response->assertNotFound();
});

test('rss feed returns xml content type', function () {
    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.feed', [], false));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('rss feed contains published posts', function () {
    BlogPost::factory()->published()->create([
        'title' => 'Feed Post',
        'body' => 'Feed body',
        'published_at' => now()->subDay(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.feed', [], false));

    $response->assertOk();
    $response->assertSee('Feed Post');
});
