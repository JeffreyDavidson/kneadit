<?php

use Illuminate\Support\Facades\Route;
use RuntimeException;

beforeEach(fn () => setUpTenantTest());

test('unexpected API exceptions do not expose internal details', function () {
    Route::get('/api/test-exception', fn () => throw new RuntimeException('database password leaked'));

    $response = $this->getJson('/api/test-exception');

    $response->assertStatus(500)
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('errors.0.status', '500')
        ->assertJsonPath('errors.0.title', 'Internal Server Error')
        ->assertJsonPath('errors.0.detail', 'An unexpected error occurred.')
        ->assertJsonMissing(['detail' => 'database password leaked']);
});
