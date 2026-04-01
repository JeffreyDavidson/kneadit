<?php

use App\Actions\Customers\CreateCateringInquiry;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
