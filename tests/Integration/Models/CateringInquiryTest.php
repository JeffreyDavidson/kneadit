<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('status label accessor returns enum label', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
    ]);

    expect($inquiry->status_label)->toBe('New Inquiry');
});

test('event type label accessor returns enum label', function () {
    $inquiry = CateringInquiry::factory()->create();

    expect($inquiry->event_type_label)->toBeString()
        ->and($inquiry->event_type_label)->not->toBeEmpty();
});
