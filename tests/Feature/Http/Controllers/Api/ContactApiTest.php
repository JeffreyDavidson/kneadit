<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('contact endpoint creates a message', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/contact', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Feedback',
            'message' => 'I love your bakery!',
        ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Message sent successfully.');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('contact endpoint validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/contact', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
});
