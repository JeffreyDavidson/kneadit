<?php

use App\Actions\Customers\ApplyCateringDepositPayment;
use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('records the payment on a quoted inquiry and confirms it', function () {
    $inquiry = CateringInquiry::factory()->quoted()->create();

    resolve(ApplyCateringDepositPayment::class)($inquiry, 'cs_test_1', 'pi_test_1', 125.00);

    $inquiry->refresh();
    expect($inquiry->deposit_amount?->dollars())->toBe(125.00)
        ->and($inquiry->deposit_reference)->toBe('pi_test_1')
        ->and($inquiry->stripe_payment_intent_id)->toBe('pi_test_1')
        ->and($inquiry->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('checks the stored inquiry, not the copy it was handed, so a deposit recorded meanwhile is not overwritten', function () {
    $owner = User::factory()->owner()->create();
    $staleCopy = CateringInquiry::factory()->quoted()->create();
    CateringInquiry::query()->whereKey($staleCopy->id)->update([
        'deposit_amount' => 10000,
        'deposit_paid_at' => now(),
        'deposit_reference' => 'pi_test_first',
        'stripe_payment_intent_id' => 'pi_test_first',
    ]);

    resolve(ApplyCateringDepositPayment::class)($staleCopy, 'cs_test_2', 'pi_test_second', 125.00);

    expect($staleCopy->refresh()->deposit_reference)->toBe('pi_test_first')
        ->and($staleCopy->deposit_amount?->dollars())->toBe(100.00)
        ->and($owner->notifications()->count())->toBe(1);
});
