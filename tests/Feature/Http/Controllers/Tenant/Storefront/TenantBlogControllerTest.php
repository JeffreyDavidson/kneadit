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
        ->assertViewIs('tenant.storefront.blog.index')
        ->assertSee('My First Recipe')
        ->assertDontSee('Draft Post');
});

test('blog post body renders the editor HTML instead of showing the tags', function () {
    $post = TenantBlogPost::factory()->published()->create([
        'body' => '<p>Fresh <strong>sourdough</strong> every Friday.</p><h2>Starter care</h2><ul><li>Feed daily</li></ul>',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.show', $post->slug, false));

    $response->assertOk()
        ->assertSeeHtml('<p>Fresh <strong>sourdough</strong> every Friday.</p>')
        ->assertSeeHtml('<h2>Starter care</h2>')
        ->assertSeeHtml('<li>Feed daily</li>')
        ->assertDontSeeHtml('&lt;p&gt;')
        ->assertDontSeeHtml('&lt;strong&gt;');
});

test('blog post body drops scripts and event handlers', function (string $body) {
    $post = TenantBlogPost::factory()->published()->create(['body' => $body]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.show', $post->slug, false));

    $response->assertOk()
        ->assertSeeHtml('<p>Safe text</p>')
        ->assertDontSeeHtml('alert(1)')
        ->assertDontSeeHtml('onerror')
        ->assertDontSeeHtml('javascript:');
})->with([
    'script element' => ['<p>Safe text</p><script>alert(1)</script>'],
    'image event handler' => ['<p>Safe text</p><img src="x" onerror="alert(1)">'],
    'javascript link' => ['<p>Safe text</p><a href="javascript:alert(1)">Click</a>'],
]);

test('blog post written as plain text still renders as paragraphs with bold', function () {
    $post = TenantBlogPost::factory()->published()->create([
        'body' => "First paragraph with **bold** text.\n\nSecond paragraph.",
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog.show', $post->slug, false));

    $response->assertOk()
        ->assertSeeHtml('<p>First paragraph with <strong>bold</strong> text.</p>')
        ->assertSeeHtml('<p>Second paragraph.</p>');
});
