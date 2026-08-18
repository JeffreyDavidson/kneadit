<?php

use App\Models\Customers\CateringInquiry;
use App\Services\Stripe\CateringDepositCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the success view without finalizing checkout when no session_id is provided', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => null]);

    test()->mock(CateringDepositCheckoutService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('handleCheckoutComplete');
    });

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]))
        ->assertOk()
        ->assertViewIs('storefront.catering.deposit-success')
        ->assertViewHas('paid', false);
});

test('skips the checkout finalize call when the inquiry already has a deposit_paid_at', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => now()]);

    test()->mock(CateringDepositCheckoutService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('handleCheckoutComplete');
    });

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]) . '?session_id=cs_test_123')
        ->assertOk()
        ->assertViewIs('storefront.catering.deposit-success')
        ->assertViewHas('paid', true);
});

test('finalizes the checkout via the service when session_id is provided and deposit is unpaid', function () {
    $inquiry = CateringInquiry::factory()->create(['deposit_paid_at' => null]);

    test()->mock(CateringDepositCheckoutService::class, function (MockInterface $mock) use ($inquiry) {
        $mock->shouldReceive('handleCheckoutComplete')
            ->once()
            ->with('cs_test_abc')
            ->andReturnUsing(function () use ($inquiry) {
                $inquiry->forceFill(['deposit_paid_at' => now()])->save();

                return $inquiry->fresh();
            });
    });

    withoutMiddleware(tenantMiddleware())
        ->get(route('catering.stripe.success', ['inquiry' => $inquiry]) . '?session_id=cs_test_abc')
        ->assertOk()
        ->assertViewHas('paid', true);
});
