<?php

use App\Services\Settings\TenantSettings;

test('it can be constructed with all properties', function () {
    $settings = new TenantSettings(
        storeName: 'Test Bakery',
        storeEmail: 'test@bakery.com',
        storePhone: '555-1234',
        storeAddress: '123 Main St',
        storeWebsite: 'https://test.com',
        storeLogo: 'logos/test.png',
        storeTagline: 'Best baked goods',
        brandColorPrimary: '#d4920c',
        onboardingCompletedAt: null,
        storefrontTheme: 'classic',
        businessTagline: 'Fresh daily',
        aboutUsText: 'We bake.',
        heroImage: 'heroes/main.jpg',
        heroStyle: 'split',
        heroTagline: 'Where every bite tells a story',
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: 'May contain nuts.',
        cateringHeroImage: 'heroes/catering.jpg',
        loyaltyHeroImage: 'heroes/loyalty.jpg',
        giftCardsHeroImage: 'heroes/gifts.jpg',
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
        cateringEventTypes: ['Wedding', 'Corporate Event'],
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
        defaultDailyCapacity: 20,
    );

    expect($settings->storeName)->toBe('Test Bakery')
        ->and($settings->storeEmail)->toBe('test@bakery.com')
        ->and($settings->leadTimeHours)->toBe(24)
        ->and($settings->deliveryEnabled)->toBeTrue()
        ->and($settings->loyaltyEnabled)->toBeTrue();
});

function makeTenantSettings(array $overrides = []): TenantSettings
{
    return new TenantSettings(...array_merge([
        'storeName' => 'Test Bakery',
        'storeEmail' => null,
        'storePhone' => null,
        'storeAddress' => null,
        'storeWebsite' => null,
        'storeLogo' => null,
        'storeTagline' => null,
        'brandColorPrimary' => '#d4920c',
        'onboardingCompletedAt' => null,
        'storefrontTheme' => 'classic',
        'businessTagline' => null,
        'aboutUsText' => null,
        'heroImage' => null,
        'heroStyle' => 'split',
        'heroTagline' => null,
        'heroPrimaryCtaText' => 'Order Now',
        'heroSecondaryCtaText' => 'Browse Menu',
        'allergyDisclaimer' => null,
        'cateringHeroImage' => null,
        'loyaltyHeroImage' => null,
        'giftCardsHeroImage' => null,
        'leadTimeHours' => 24,
        'deliveryEnabled' => true,
        'freeDeliveryMinimum' => '50',
        'minimumPickupOrderAmount' => '0',
        'minimumDeliveryOrderAmount' => '0',
        'deliveryFeeTiers' => [],
        'paymentMethodsAccepted' => [],
        'operatingHours' => [],
        'faqItems' => [],
        'loyaltyProgramName' => 'Rewards',
        'loyaltyPointsPerDollar' => '10',
        'loyaltyEnabled' => true,
        'cateringMinimumGuests' => '10',
        'cateringLeadTimeDays' => '14',
        'cateringEventTypes' => ['Wedding', 'Corporate Event'],
        'socialMediaLinks' => [],
        'homepageSections' => [],
        'cateringEnabled' => false,
        'storePhoto' => null,
        'announcementEnabled' => false,
        'announcementText' => '',
        'announcementType' => 'info',
        'showPolicies' => false,
        'cancellationPolicy' => '',
        'depositPolicy' => '',
        'refundPolicy' => '',
        'pickupPolicy' => '',
        'additionalTerms' => '',
        'birthdayProgramEnabled' => false,
        'birthdayCouponEnabled' => false,
        'birthdayDiscountPercentage' => 15,
        'birthdayCouponValidDays' => 7,
        'reviewRequestsEnabled' => false,
        'reviewRequestDelayHours' => 24,
        'repeatRemindersEnabled' => false,
        'repeatReminderDays' => 30,
        'giftCardPresetAmounts' => [10, 25, 50, 100],
        'giftCardDefaultAmount' => 25,
        'defaultDailyCapacity' => 20,
    ], $overrides));
}

test('heroImageUrl returns storage url when hero image is set', function () {
    Storage::fake('public');
    $settings = makeTenantSettings(['heroImage' => 'heroes/main.jpg']);

    expect($settings->heroImageUrl())->toContain('heroes/main.jpg');
});

test('heroImageUrl returns default unsplash url when hero image is null', function () {
    $settings = makeTenantSettings(['heroImage' => null]);

    expect($settings->heroImageUrl())->toContain('unsplash.com');
});

test('cateringHeroImageUrl returns catering-specific fallback when null', function () {
    $settings = makeTenantSettings(['cateringHeroImage' => null]);

    expect($settings->cateringHeroImageUrl())->toContain('photo-1555244162');
});

test('loyaltyHeroImageUrl returns default fallback when null', function () {
    $settings = makeTenantSettings(['loyaltyHeroImage' => null]);

    expect($settings->loyaltyHeroImageUrl())->toContain('unsplash.com');
});

test('giftCardsHeroImageUrl returns default fallback when null', function () {
    $settings = makeTenantSettings(['giftCardsHeroImage' => null]);

    expect($settings->giftCardsHeroImageUrl())->toContain('unsplash.com');
});

test('storeLogoUrl returns asset url when logo is set', function () {
    $settings = makeTenantSettings(['storeLogo' => 'logos/test.png']);

    expect($settings->storeLogoUrl())->toContain('storage/logos/test.png');
});

test('storeLogoUrl returns null when logo is not set', function () {
    $settings = makeTenantSettings(['storeLogo' => null]);

    expect($settings->storeLogoUrl())->toBeNull();
});

test('defaultTagline returns store tagline when set', function () {
    $settings = makeTenantSettings(['storeTagline' => 'Custom tagline']);

    expect($settings->defaultTagline())->toBe('Custom tagline');
});

test('defaultTagline returns generated tagline when not set', function () {
    $settings = makeTenantSettings([
        'storeName' => 'My Bakery',
        'storeTagline' => null,
        'brandColorPrimary' => '#d4920c',
        'onboardingCompletedAt' => null,
    ]);

    expect($settings->defaultTagline())->toBe('My Bakery — Fresh baked goods made with love');
});

test('visibleHomepageSections filters and sorts by order', function () {
    $settings = makeTenantSettings(['homepageSections' => [
        'hero' => ['visible' => true, 'order' => 3],
        'hidden' => ['visible' => false, 'order' => 1],
        'featured' => ['visible' => true, 'order' => 1],
        'no_visible_key' => ['order' => 2],
    ]]);

    $sections = $settings->visibleHomepageSections();

    expect($sections->keys()->all())->toBe(['featured', 'no_visible_key', 'hero']);
});

test('leadTimeDays calculates days from hours', function () {
    expect(makeTenantSettings(['leadTimeHours' => 24])->leadTimeDays())->toBe(1)
        ->and(makeTenantSettings(['leadTimeHours' => 48])->leadTimeDays())->toBe(2)
        ->and(makeTenantSettings(['leadTimeHours' => 36])->leadTimeDays())->toBe(2);
});

test('brandColorPrimary is accessible', function () {
    $settings = makeTenantSettings(['brandColorPrimary' => '#ff5500']);

    expect($settings->brandColorPrimary)->toBe('#ff5500');
});

test('onboardingCompletedAt is accessible and nullable', function () {
    $withValue = makeTenantSettings(['onboardingCompletedAt' => '2026-01-15 10:00:00']);
    $withNull = makeTenantSettings(['onboardingCompletedAt' => null]);

    expect($withValue->onboardingCompletedAt)->toBe('2026-01-15 10:00:00')
        ->and($withNull->onboardingCompletedAt)->toBeNull();
});

test('cateringEventTypes falls back to enum default labels when not configured', function () {
    setUpTenantTest();

    $settings = TenantSettings::resolve();

    expect($settings->cateringEventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cateringEventTypes resolves from a stored json array', function () {
    setUpTenantTest();
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);

    $settings = TenantSettings::resolve();

    expect($settings->cateringEventTypes)->toBe(['Kids Party', 'School Function']);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cateringEventTypes falls back to defaults when the stored json is empty', function () {
    setUpTenantTest();
    settings(['catering_event_types' => json_encode([])]);

    $settings = TenantSettings::resolve();

    expect($settings->cateringEventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('giftCardPresetAmounts defaults to standard amounts', function () {
    $settings = makeTenantSettings();

    expect($settings->giftCardPresetAmounts)->toBe([10, 25, 50, 100]);
});

test('giftCardPresetAmounts can be customized', function () {
    $settings = makeTenantSettings(['giftCardPresetAmounts' => [5, 15, 30, 75]]);

    expect($settings->giftCardPresetAmounts)->toBe([5, 15, 30, 75]);
});

test('giftCardDefaultAmount defaults to 25', function () {
    $settings = makeTenantSettings();

    expect($settings->giftCardDefaultAmount)->toBe(25);
});

test('giftCardDefaultAmount can be customized', function () {
    $settings = makeTenantSettings(['giftCardDefaultAmount' => 50]);

    expect($settings->giftCardDefaultAmount)->toBe(50);
});

test('hero CTA and tagline properties are accessible', function () {
    $settings = makeTenantSettings([
        'heroTagline' => 'Freshly baked daily',
        'heroPrimaryCtaText' => 'Place Your Order',
        'heroSecondaryCtaText' => 'See What\'s Fresh',
    ]);

    expect($settings->heroTagline)->toBe('Freshly baked daily')
        ->and($settings->heroPrimaryCtaText)->toBe('Place Your Order')
        ->and($settings->heroSecondaryCtaText)->toBe('See What\'s Fresh');
});

test('hero CTA properties fall back to defaults when unset in settings', function () {
    setUpTenantTest();

    $settings = TenantSettings::resolve();

    expect($settings->heroPrimaryCtaText)->toBe('Order Now')
        ->and($settings->heroSecondaryCtaText)->toBe('Browse Menu')
        ->and($settings->heroTagline)->toBeNull();
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it is bound as a singleton in the container', function () {
    setUpTenantTest();

    $a = app(TenantSettings::class);
    $b = app(TenantSettings::class);

    expect($a)->toBeInstanceOf(TenantSettings::class)
        ->and($a)->toBe($b);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
