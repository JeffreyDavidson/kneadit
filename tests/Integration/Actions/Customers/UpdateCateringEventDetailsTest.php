<?php

use App\Actions\Customers\UpdateCateringEventDetails;
use App\DataTransferObjects\Customers\CateringEventDetails;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('updates catering event details', function () {
    $inquiry = CateringInquiry::factory()->create();
    $details = new CateringEventDetails(
        eventType: 'Corporate Event',
        eventDate: '2026-10-15',
        guestCount: 85,
        budget: 2500.50,
        details: 'Passed appetizers and a plated dinner.',
        dietaryRequirements: 'Two gluten-free meals.',
        venueAddress: '123 Market Street',
    );

    resolve(UpdateCateringEventDetails::class)($inquiry, $details);

    $inquiry->refresh();

    expect($inquiry->event_type)->toBe('Corporate Event')
        ->and($inquiry->event_date?->toDateString())->toBe('2026-10-15')
        ->and($inquiry->guest_count)->toBe(85)
        ->and($inquiry->budget?->dollars())->toBe(2500.50)
        ->and($inquiry->details)->toBe('Passed appetizers and a plated dinner.')
        ->and($inquiry->dietary_requirements)->toBe('Two gluten-free meals.')
        ->and($inquiry->venue_address)->toBe('123 Market Street');
});
