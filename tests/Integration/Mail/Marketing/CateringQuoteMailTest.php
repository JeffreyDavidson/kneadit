<?php

use App\Mail\Marketing\CateringQuoteMail;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the itemized table when the inquiry has items', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_name' => 'Maya Patel',
        'event_type' => 'Wedding',
        'guest_count' => 80,
        'event_date' => now()->addMonths(3),
    ]);

    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Wedding cake',
        'quantity' => 1,
        'unit_price' => 400,
    ]);
    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Macarons',
        'quantity' => 100,
        'unit_price' => 3,
    ]);

    $html = (new CateringQuoteMail($inquiry->refresh()))->render();

    expect($html)->toContain("What's included")
        ->and($html)->toContain('Wedding cake')
        ->and($html)->toContain('Macarons')
        ->and($html)->toContain('100');
});

test('falls back to single-amount layout when the inquiry has no items', function () {
    $inquiry = CateringInquiry::factory()->create([
        'event_type' => 'Birthday Party',
        'event_date' => now()->addMonth(),
        'quoted_amount' => 750,
    ]);

    $html = (new CateringQuoteMail($inquiry))->render();

    expect($html)->not->toContain("What's included")
        ->and($html)->toContain('Your Quote');
});
