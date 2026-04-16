<?php

use App\Http\Middleware\EnsureStorefrontEnabled;
use Illuminate\Http\Request;

test('middleware passes when no tenant context', function () {
    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects to external website when storefront disabled and external url set', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->storefront_enabled = false;
    $tenant->external_website = 'https://mybakery.com';

    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe('https://mybakery.com');
});

test('disabled storefront view receives storeName from TenantSettings', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->storefront_enabled = false;
    $tenant->external_website = null;

    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $settings = new App\Services\Settings\TenantSettings(
        storeName: 'Mock Bakery',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#d4920c',
        onboardingCompletedAt: null,
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'split',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
        leadTimeHours: 24,
        deliveryEnabled: true,
        freeDeliveryMinimum: '50',
        minimumPickupOrderAmount: '0',
        minimumDeliveryOrderAmount: '0',
        deliveryFeeTiers: [],
        paymentMethodsAccepted: [],
        operatingHours: [],
        faqItems: [],
        loyaltyProgramName: 'Rewards',
        loyaltyPointsPerDollar: '10',
        loyaltyEnabled: true,
        cateringMinimumGuests: '10',
        cateringLeadTimeDays: '14',
        socialMediaLinks: [],
        homepageSections: [],
        cateringEnabled: false,
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
    );
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toContain('Mock Bakery');
});
