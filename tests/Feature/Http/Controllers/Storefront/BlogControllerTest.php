<?php

use App\Models\Content\TenantBlogPost;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('blog index page renders', function () {
    TenantBlogPost::factory()->published()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog', [], false));

    $response->assertOk();
});
