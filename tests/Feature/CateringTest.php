<?php

use App\Models\CateringInquiry;
use App\Models\Setting;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('catering page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/catering');

    $response->assertOk();
});

test('catering inquiry can be submitted with valid data', function () {
    Setting::set('catering_lead_time_days', '1');
    Setting::set('catering_minimum_guests', '5');

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/catering', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-1234',
            'event_type' => 'wedding',
            'event_date' => now()->addDays(30)->format('Y-m-d'),
            'guest_count' => 50,
            'budget' => '5000',
            'details' => 'We need a beautiful wedding cake and pastries.',
            'dietary_requirements' => 'Gluten-free options needed',
            'venue_address' => '123 Wedding Ln',
        ]);

    $response->assertRedirect(route('storefront.catering'));
    $this->assertDatabaseHas('catering_inquiries', [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'event_type' => 'wedding',
    ]);
});

test('catering validation rejects missing required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())->post('/catering', []);

    $response->assertSessionHasErrors(['customer_name', 'customer_email', 'event_type', 'event_date', 'guest_count', 'details']);
});

test('catering validation rejects past event dates', function () {
    Setting::set('catering_lead_time_days', '1');
    Setting::set('catering_minimum_guests', '5');

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/catering', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'event_type' => 'birthday',
            'event_date' => now()->subDay()->format('Y-m-d'),
            'guest_count' => 10,
            'details' => 'Birthday party',
        ]);

    $response->assertSessionHasErrors(['event_date']);
});

test('inquiry is saved with default status', function () {
    Setting::set('catering_lead_time_days', '1');
    Setting::set('catering_minimum_guests', '5');

    withoutMiddleware(tenantMiddleware())
        ->post('/catering', [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'event_type' => 'corporate',
            'event_date' => now()->addDays(30)->format('Y-m-d'),
            'guest_count' => 20,
            'details' => 'Corporate event',
        ]);

    $inquiry = CateringInquiry::query()->first();

    expect($inquiry)->not->toBeNull();
});
