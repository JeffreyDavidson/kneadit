<?php

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('valid code stores in session and redirects with a success flash', function () {
    settings(['customer_referral_program_enabled' => true, 'customer_referral_discount_dollars' => 10]);
    Customer::factory()->create(['name' => 'Alice', 'referral_code' => 'ABC12345']);

    $response = withoutMiddleware(tenantMiddleware())->get('/referral/ABC12345');

    $response->assertRedirect(route('order.create'));
    $response->assertSessionHas('referral_code', 'ABC12345');
    $response->assertSessionHas('success');
});

test('unknown code redirects with an error flash', function () {
    settings(['customer_referral_program_enabled' => true]);

    $response = withoutMiddleware(tenantMiddleware())->get('/referral/NOTACODE');

    $response->assertRedirect(route('order.create'));
    $response->assertSessionMissing('referral_code');
    $response->assertSessionHasErrors(['referral']);
});

test('disabled feature redirects without storing the code', function () {
    settings(['customer_referral_program_enabled' => false]);
    Customer::factory()->create(['referral_code' => 'ABC12345']);

    $response = withoutMiddleware(tenantMiddleware())->get('/referral/ABC12345');

    $response->assertRedirect(route('order.create'));
    $response->assertSessionMissing('referral_code');
});
