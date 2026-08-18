<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('pay-deposit endpoint rejects unsigned requests', function () {
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => 1000.00]);

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/{$inquiry->id}/pay-deposit");

    $response->assertForbidden();
});

test('pay-deposit redirects if deposit already paid', function () {
    $inquiry = CateringInquiry::factory()->create([
        'quoted_amount' => 1000.00,
        'deposit_paid_at' => now()->subDay(),
    ]);

    $url = URL::temporarySignedRoute('catering.payDeposit', now()->addDay(), ['inquiry' => $inquiry->id]);
    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('pay-deposit 404s when no deposit is configured', function () {
    settings(['catering_deposit_percent' => 0]);
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => 1000.00]);

    $url = URL::temporarySignedRoute('catering.payDeposit', now()->addDay(), ['inquiry' => $inquiry->id]);
    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertNotFound();
});

test('success page shows "paid" state when deposit_paid_at is set', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'deposit_paid_at' => now(),
        'deposit_reference' => 'pi_test_1234',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/stripe/success/{$inquiry->id}");

    $response->assertOk();
    $response->assertSee('Deposit received');
    $response->assertSee('pi_test_1234');
});

test('success page shows "verifying" state when deposit is not yet stamped', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'deposit_paid_at' => null,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/stripe/success/{$inquiry->id}");

    $response->assertOk();
    $response->assertSee('Verifying');
});

test('cancel page renders', function () {
    $inquiry = CateringInquiry::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/stripe/cancel/{$inquiry->id}");

    $response->assertOk();
    $response->assertSee('Deposit not paid');
});
