<?php

use App\DataTransferObjects\Settings\OnboardingSettings;
use App\Enums\Customers\CateringEventType;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    app()->instance(TenantSettings::class, makeTenantSettings(
        store: makeStoreInfo(['name' => 'Test']),
        orders: makeOrderSettings(['deliveryEnabled' => false]),
        loyalty: makeLoyaltySettings(['enabled' => false]),
        catering: makeCateringSettings(['enabled' => true, 'eventTypes' => CateringEventType::defaultLabels()]),
        onboarding: new OnboardingSettings(completedAt: now()->toDateTimeString()),
    ));
});

/** @return array<string, mixed> */
function validInquiryPayload(): array
{
    return [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'event_type' => 'Wedding',
        'event_date' => now()->addDays(30)->toDateString(),
        'guest_count' => 25,
        'details' => 'Outdoor reception, 25 people, mostly vegetarian.',
    ];
}

test('successful inquiry submission redirects with default success message', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('catering.submit', [], false), validInquiryPayload());

    $response->assertRedirect()
        ->assertSessionHas('success', "Thank you for your inquiry! We'll review your request and get back to you with a custom quote soon.");
});

test('inquiry success message can be customized via page content', function () {
    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'catering' => ['flash_success' => 'Got your request — chatting soon!'],
        ]),
    ]);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('catering.submit', [], false), validInquiryPayload());

    $response->assertRedirect()
        ->assertSessionHas('success', 'Got your request — chatting soon!');
});

test('inquiry validation fails when required fields are missing', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('catering.submit', [], false), []);

    $response->assertSessionHasErrors([
        'customer_name',
        'customer_email',
        'event_type',
        'event_date',
        'guest_count',
        'details',
    ]);
});
