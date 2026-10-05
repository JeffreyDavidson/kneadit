<?php

use App\Models\Content\TenantBlogPost;

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
        ->assertViewHas('posts', fn ($posts) => $posts->count() === 20);
});

test('blog feed is well-formed XML with titles escaped once', function () {
    TenantBlogPost::factory()->published()->create([
        'title' => 'Bread & Butter',
        'slug' => 'bread-and-butter',
        'excerpt' => 'Salt & "pepper" <3',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.feed', [], false));

    $feed = simplexml_load_string($response->getContent());

    expect($feed)->not->toBeFalse()
        ->and((string) $feed->channel->link)->toBe(url('/blog'))
        ->and((string) $feed->channel->item[0]->title)->toBe('Bread & Butter')
        ->and((string) $feed->channel->item[0]->description)->toBe('Salt & "pepper" <3')
        ->and((string) $feed->channel->item[0]->link)->toBe(route('storefront.blog.show', 'bread-and-butter'));
});
