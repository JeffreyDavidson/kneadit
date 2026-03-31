<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('index validates category against allowed values', function () {
    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('blog.index', ['category' => 'malicious-value']));

    $response->assertRedirect();
});
