<?php

use App\Models\TenantBlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('blog index page renders', function () {
    TenantBlogPost::factory()->published()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog', [], false));

    $response->assertOk();
});
