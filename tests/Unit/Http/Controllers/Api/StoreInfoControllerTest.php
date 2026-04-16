<?php

use App\Http\Controllers\Api\StoreInfoController;
use App\Services\Settings\TenantSettings;

test('it returns store info from TenantSettings', function () {
    $settings = new TenantSettings(
        storeName: 'Sweet Crumbs',
        storeEmail: 'hello@sweetcrumbs.com',
        storePhone: '555-1234',
        storeAddress: '42 Baker St',
        storeWebsite: 'https://sweetcrumbs.com',
        storeLogo: 'logos/sweet.png',
        storeTagline: 'Baked with love',
        brandColorPrimary: '#e84393',
        onboardingCompletedAt: null,
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
        deliveryEnabled: true,
        freeDeliveryMinimum: '50',
        minimumPickupOrderAmount: '0',
        minimumDeliveryOrderAmount: '0',
        deliveryFeeTiers: [],
        paymentMethodsAccepted: [],
        operatingHours: ['mon' => '8am-5pm'],
        faqItems: [],
        loyaltyProgramName: 'Rewards',
        loyaltyPointsPerDollar: '10',
        loyaltyEnabled: true,
        cateringMinimumGuests: '10',
        cateringLeadTimeDays: '14',
        socialMediaLinks: ['facebook' => 'https://fb.com/sweet', 'instagram' => 'https://ig.com/sweet'],
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

    $controller = new StoreInfoController;
    $response = $controller($settings);
    $data = $response->getData(true);

    expect($data['data']['store_name'])->toBe('Sweet Crumbs')
        ->and($data['data']['email'])->toBe('hello@sweetcrumbs.com')
        ->and($data['data']['phone'])->toBe('555-1234')
        ->and($data['data']['address'])->toBe('42 Baker St');
});
