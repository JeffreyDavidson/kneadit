<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('contact endpoint accepts valid message', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/contact', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Custom cake inquiry',
            'message' => 'I have a question about custom cakes.',
        ]);

    $response->assertCreated()
        ->assertJson(['message' => 'Message sent successfully.']);
});
