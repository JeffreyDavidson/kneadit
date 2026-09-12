<?php

use App\Actions\Customers\UpdateCateringCustomerDetails;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('updates catering customer contact details', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_name' => 'Old Name',
        'customer_email' => 'old@example.com',
        'customer_phone' => null,
    ]);

    resolve(UpdateCateringCustomerDetails::class)(
        $inquiry,
        'New Name',
        'new@example.com',
        '555-0123',
    );

    $inquiry->refresh();

    expect($inquiry->customer_name)->toBe('New Name')
        ->and($inquiry->customer_email)->toBe('new@example.com')
        ->and($inquiry->customer_phone)->toBe('5550123');
});
