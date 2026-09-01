<?php

use App\Models\Customers\CateringInquiry;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the success view without finalizing checkout when no session_id is provided', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => null]);

    $checkoutService = Double::for(CateringDepositCheckoutService::class);
    $checkoutService->expects('handleCheckoutComplete')->never();
    app()->instance(CateringDepositCheckoutService::class, $checkoutService);

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]))
        ->assertOk()
        ->assertViewIs('storefront.catering.deposit-success')
        ->assertViewHas('paid', false);
});

test('skips the checkout finalize call when the inquiry already has a deposit_paid_at', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => now()]);

    $checkoutService = Double::for(CateringDepositCheckoutService::class);
    $checkoutService->expects('handleCheckoutComplete')->never();
    app()->instance(CateringDepositCheckoutService::class, $checkoutService);

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]) . '?session_id=cs_test_123')
        ->assertOk()
        ->assertViewIs('storefront.catering.deposit-success')
        ->assertViewHas('paid', true);
});

test('finalizes the checkout via the service when session_id is provided and deposit is unpaid', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => null]);

    $checkoutService = Double::for(CateringDepositCheckoutService::class);
    $checkoutService->expects('handleCheckoutComplete')
        ->with('cs_test_abc')
        ->resolves(function () use ($inquiry): CateringInquiry {
            $inquiry->forceFill(['deposit_paid_at' => now()])->save();

            return $inquiry->fresh();
        });
    app()->instance(CateringDepositCheckoutService::class, $checkoutService);

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]) . '?session_id=cs_test_abc')
        ->assertOk()
        ->assertViewHas('paid', true);
});
