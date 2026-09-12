<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('contact endpoint accepts a valid message and returns 204', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/contact', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Custom cake inquiry',
            'message' => 'I have a question about custom cakes.',
        ]);

    $response->assertNoContent();
});
