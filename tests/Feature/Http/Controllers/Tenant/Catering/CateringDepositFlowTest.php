<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use JMac\Testing\Double;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function signedPayDepositUrl(CateringInquiry $inquiry): string
{
    return URL::temporarySignedRoute('catering.payDeposit', now()->addDay(), ['inquiry' => $inquiry->id]);
}

test('pay-deposit endpoint rejects unsigned requests', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create(['quoted_amount' => 1000.00]);

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/{$inquiry->id}/pay-deposit");

    $response->assertForbidden();
});

test('pay-deposit sends the bakery storefront a thank-you when the deposit is already paid', function () {
    $inquiry = CateringInquiry::factory()->confirmed()->create([
        'quoted_amount' => 1000.00,
        'deposit_paid_at' => now()->subDay(),
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(signedPayDepositUrl($inquiry));

    $response->assertRedirect(route('storefront.catering'));
    $response->assertSessionHas('success');
});

test('pay-deposit 404s when no deposit is configured', function () {
    settings(['catering_deposit_percent' => 0]);
    $inquiry = CateringInquiry::factory()->quoted()->create(['quoted_amount' => 1000.00]);

    $response = withoutMiddleware(tenantMiddleware())->get(signedPayDepositUrl($inquiry));

    $response->assertNotFound();
});

test('pay-deposit redirects to checkout while the quote is open', function (CateringInquiryStatus $status) {
    $inquiry = CateringInquiry::factory()->create(['status' => $status, 'quoted_amount' => 1000.00]);
    $checkout = Double::for(CateringDepositCheckoutService::class);
    $checkout->expects('redirectToCheckout')->returns('https://checkout.stripe.test/c/pay/cs_test_1');
    app()->instance(CateringDepositCheckoutService::class, $checkout);

    $response = withoutMiddleware(tenantMiddleware())->get(signedPayDepositUrl($inquiry));

    $response->assertRedirect('https://checkout.stripe.test/c/pay/cs_test_1');
})->with([
    'quoted' => CateringInquiryStatus::Quoted,
    'confirmed' => CateringInquiryStatus::Confirmed,
]);

test('pay-deposit shows a friendly page and starts no checkout once the quote is closed', function (CateringInquiryStatus $status) {
    $inquiry = CateringInquiry::factory()->create(['status' => $status, 'quoted_amount' => 1000.00]);
    $checkout = Double::for(CateringDepositCheckoutService::class);
    $checkout->expects('redirectToCheckout')->never();
    app()->instance(CateringDepositCheckoutService::class, $checkout);

    $response = withoutMiddleware(tenantMiddleware())->get(signedPayDepositUrl($inquiry));

    $response->assertStatus(410);
    $response->assertSee('no longer open for payment');
})->with([
    'inquiry' => CateringInquiryStatus::Inquiry,
    'completed' => CateringInquiryStatus::Completed,
    'cancelled' => CateringInquiryStatus::Cancelled,
]);

test('success page shows "paid" state when deposit_paid_at is set', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'deposit_paid_at' => now(),
        'deposit_reference' => 'pi_test_1234',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(URL::signedRoute('catering.stripe.success', ['inquiry' => $inquiry]));

    $response->assertOk();
    $response->assertSee('Deposit received');
    $response->assertSee('pi_test_1234');
});

test('success page shows "verifying" state when deposit is not yet stamped', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'deposit_paid_at' => null,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(URL::signedRoute('catering.stripe.success', ['inquiry' => $inquiry]));

    $response->assertOk();
    $response->assertSee('Verifying');
});

test('success page without a valid signature reveals nothing about the inquiry', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_name' => 'Pat Private',
        'status' => CateringInquiryStatus::Confirmed,
        'deposit_paid_at' => now(),
        'deposit_reference' => 'pi_test_1234',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/stripe/success/{$inquiry->id}");

    $response->assertForbidden();
    $response->assertDontSee('Pat Private');
    $response->assertDontSee('pi_test_1234');
});

test('cancel page renders', function () {
    $inquiry = CateringInquiry::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())->get(URL::signedRoute('catering.stripe.cancel', ['inquiry' => $inquiry]));

    $response->assertOk();
    $response->assertSee('Deposit not paid');
});

test('cancel page without a valid signature is forbidden', function () {
    $inquiry = CateringInquiry::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())->get("/catering/stripe/cancel/{$inquiry->id}");

    $response->assertForbidden();
});
