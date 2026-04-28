<?php

use App\Actions\Customers\CancelCateringInquiry;
use App\Enums\Customers\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions inquiry to cancelled', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Quoted]);

    resolve(CancelCateringInquiry::class)($inquiry);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Cancelled);
    Event::assertNotDispatched(CateringQuoteRequested::class);
});

test('prepends a stamped reason to notes when provided', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'notes' => 'Existing internal note.',
    ]);

    resolve(CancelCateringInquiry::class)($inquiry, 'Customer chose another vendor');

    $notes = $inquiry->fresh()->notes;
    expect($notes)->toContain('Cancelled: Customer chose another vendor')
        ->and($notes)->toContain('Existing internal note.')
        ->and(str_starts_with($notes, '['))->toBeTrue();
});

test('ignores blank reasons', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'notes' => 'Existing.',
    ]);

    resolve(CancelCateringInquiry::class)($inquiry, '   ');

    expect($inquiry->fresh()->notes)->toBe('Existing.');
});
