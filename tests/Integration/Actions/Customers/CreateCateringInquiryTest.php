<?php

use App\Actions\Customers\CreateCateringInquiry;
use App\Events\Marketing\CateringInquiryReceived;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it creates a catering inquiry', function () {
    $action = new CreateCateringInquiry;
    $inquiry = $action([
        'customer_name' => 'Jane Baker',
        'customer_email' => 'jane@example.com',
        'customer_phone' => '555-1234',
        'event_type' => 'wedding',
        'event_date' => '2026-06-15',
        'guest_count' => 50,
        'details' => 'We need a wedding cake.',
    ]);

    expect($inquiry)->toBeInstanceOf(CateringInquiry::class)
        ->and($inquiry->customer_name)->toBe('Jane Baker')
        ->and($inquiry->customer_email)->toBe('jane@example.com');
});

test('fires CateringInquiryReceived after creation', function () {
    Event::fake();

    $inquiry = resolve(CreateCateringInquiry::class)([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'event_type' => 'Wedding',
        'event_date' => now()->addMonth()->format('Y-m-d'),
        'guest_count' => 50,
        'details' => 'Outdoor reception for 50 — passed appetizers preferred.',
    ]);

    Event::assertDispatched(
        CateringInquiryReceived::class,
        fn (CateringInquiryReceived $e): bool => $e->inquiry->is($inquiry),
    );
});
