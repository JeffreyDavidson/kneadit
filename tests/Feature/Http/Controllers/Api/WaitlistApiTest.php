<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('API creates a waitlist entry and returns JSON:API envelope', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/waitlist', [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'requested_date' => now()->addDays(5)->toDateString(),
        ]);

    $response->assertCreated()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'waitlist-entries')
        ->assertJsonStructure([
            'data' => ['id', 'type', 'attributes' => ['customer_name', 'customer_email', 'requested_date', 'status']],
        ]);
});

test('API rejects a waitlist submission missing required fields with 422', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/waitlist', []);

    $response->assertStatus(422);

    $errors = $response->json('errors');

    throw_unless(is_array($errors), UnexpectedValueException::class, 'Expected JSON:API validation errors.');

    $pointers = collect($errors)->pluck('source.pointer')->all();
    expect($pointers)->toContain(
        '/data/attributes/customer_name',
        '/data/attributes/customer_email',
        '/data/attributes/customer_phone',
        '/data/attributes/requested_date',
    );
});
