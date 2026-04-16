<?php

use App\Http\Controllers\Storefront\AppIconController;
use App\Services\Settings\TenantSettings;
use App\Services\Support\AppIconGeneratorService;

test('it generates icon using brandColorPrimary from TenantSettings', function () {
    $settings = new TenantSettings(
        storeName: 'Test',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#ff5500',
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

    $controller = new AppIconController;
    $response = $controller('192', $settings, new AppIconGeneratorService);

    // Verify the image background uses the settings color (#ff5500 = rgb(255, 85, 0))
    $img = imagecreatefromstring($response->getContent());
    $rgb = imagecolorat($img, 0, 0);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    imagedestroy($img);

    expect($r)->toBe(255)
        ->and($g)->toBe(85)
        ->and($b)->toBe(0);
});
