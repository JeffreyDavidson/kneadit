<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('API creates a waitlist entry', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/waitlist', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'requested_date' => now()->addDays(5)->toDateString(),
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id'], 'message']);
});
