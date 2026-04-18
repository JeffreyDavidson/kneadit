<?php

use App\Enums\Customers\CateringEventType;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    app()->instance(TenantSettings::class, new TenantSettings(
        storeName: 'Test',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#d4920c',
        onboardingCompletedAt: now()->toDateTimeString(),
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'split',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
        leadTimeHours: 24,
        deliveryEnabled: false,
        freeDeliveryMinimum: '50',
        minimumPickupOrderAmount: '0',
        minimumDeliveryOrderAmount: '0',
        deliveryFeeTiers: [],
        paymentMethodsAccepted: [],
        operatingHours: [],
        faqItems: [],
        loyaltyProgramName: 'Rewards',
        loyaltyPointsPerDollar: '10',
        loyaltyEnabled: false,
        cateringMinimumGuests: '10',
        cateringLeadTimeDays: '14',
        cateringEventTypes: CateringEventType::defaultLabels(),
        socialMediaLinks: [],
        homepageSections: [],
        cateringEnabled: true,
        storePhoto: null,
        announcementEnabled: false,
        announcementText: '',
        announcementType: 'info',
        showPolicies: false,
        cancellationPolicy: '',
        depositPolicy: '',
        refundPolicy: '',
        pickupPolicy: '',
        additionalTerms: '',
        birthdayProgramEnabled: false,
        birthdayCouponEnabled: false,
        birthdayDiscountPercentage: 15,
        birthdayCouponValidDays: 7,
        reviewRequestsEnabled: false,
        reviewRequestDelayHours: 24,
        repeatRemindersEnabled: false,
        repeatReminderDays: 30,
        giftCardPresetAmounts: [10, 25, 50, 100],
        giftCardDefaultAmount: 25,
        defaultDailyCapacity: 20,
    ));
});

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
