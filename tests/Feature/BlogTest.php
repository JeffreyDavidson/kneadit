<?php

use App\Models\BlogPost;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpCentralTest();
});

test('blog index page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/blog');

    $response->assertOk();
});

test('blog shows only published posts', function () {
    BlogPost::create([
        'title' => 'Published Post',
        'body' => 'Content here',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    BlogPost::create([
        'title' => 'Draft Post',
        'body' => 'Draft content',
        'is_published' => false,
        'published_at' => null,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/blog');

    $response->assertOk();
    $response->assertSee('Published Post');
    $response->assertDontSee('Draft Post');
});

test('blog hides draft posts', function () {
    BlogPost::create([
        'title' => 'My Draft',
        'body' => 'Not ready yet',
        'is_published' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/blog');

    $response->assertOk();
    $response->assertDontSee('My Draft');
});

test('blog paginates at six per page', function () {
    for ($i = 1; $i <= 8; $i++) {
        BlogPost::create([
            'title' => "Post Number $i",
            'body' => "Body $i",
            'is_published' => true,
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = withoutMiddleware(tenantMiddleware())->get('/blog');

    $response->assertOk();
    $response->assertSee('Post Number 1');
    $response->assertSee('Post Number 6');
    $response->assertDontSee('Post Number 7');
});

test('individual blog post loads by slug', function () {
    BlogPost::create([
        'title' => 'Sourdough Tips',
        'slug' => 'sourdough-tips',
        'body' => 'Here are some tips for sourdough.',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/blog/sourdough-tips');

    $response->assertOk();
    $response->assertSee('Sourdough Tips');
});

test('returns 404 for nonexistent slug', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/blog/does-not-exist');

    $response->assertNotFound();
});

test('returns 404 for draft post slug', function () {
    BlogPost::create([
        'title' => 'Secret Draft',
        'slug' => 'secret-draft',
        'body' => 'Hidden content',
        'is_published' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/blog/secret-draft');

    $response->assertNotFound();
});

test('rss feed returns xml content type', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/blog/feed.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('rss feed contains published posts', function () {
    BlogPost::create([
        'title' => 'Feed Post',
        'body' => 'Feed body',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/blog/feed.xml');

    $response->assertOk();
    $response->assertSee('Feed Post');
});
