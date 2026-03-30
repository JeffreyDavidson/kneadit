<?php

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('API validates a valid coupon', function () {
    Coupon::factory()->percentage()->create([
        'code' => 'API10OFF',
        'value' => 10,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'API10OFF',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.valid', true);
});
