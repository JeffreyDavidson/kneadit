<?php

use App\Http\Controllers\Storefront\ManifestController;
use App\Services\Settings\TenantSettings;

test('it uses brandColorPrimary from TenantSettings for theme_color', function () {
    $settings = new TenantSettings(
        storeName: 'Test Bakery',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#ff5500',
        onboardingCompletedAt: null,
        storefrontTheme: 'classic',
        businessTagline: 'Fresh daily',
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
        giftCardPresetAmounts: [10, 25, 50, 100],
        giftCardDefaultAmount: 25,
    );

    $controller = new ManifestController;
    $response = $controller($settings);
    $data = $response->getData(true);

    expect($data['theme_color'])->toBe('#ff5500')
        ->and($data['name'])->toBe('Test Bakery');
});
