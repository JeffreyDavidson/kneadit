<?php

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('blog index page renders', function () {
    BlogPost::factory()->create(['is_published' => true, 'published_at' => now()]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.blog', [], false));

    $response->assertOk();
});
