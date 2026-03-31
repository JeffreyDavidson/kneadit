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
        storefrontTheme: 'classic',
        businessTagline: 'Fresh daily',
        aboutUsText: 'We bake.',
        heroImage: 'heroes/main.jpg',
        heroStyle: 'split',
        allergyDisclaimer: 'May contain nuts.',
        cateringHeroImage: 'heroes/catering.jpg',
        loyaltyHeroImage: 'heroes/loyalty.jpg',
        giftCardsHeroImage: 'heroes/gifts.jpg',
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
        'storefrontTheme' => 'classic',
        'businessTagline' => null,
        'aboutUsText' => null,
        'heroImage' => null,
        'heroStyle' => 'split',
        'allergyDisclaimer' => null,
        'cateringHeroImage' => null,
        'loyaltyHeroImage' => null,
        'giftCardsHeroImage' => null,
        'leadTimeHours' => 24,
        'deliveryEnabled' => true,
        'freeDeliveryMinimum' => '50',
        'deliveryFeeTiers' => [],
        'paymentMethodsAccepted' => [],
        'operatingHours' => [],
        'faqItems' => [],
        'loyaltyProgramName' => 'Rewards',
        'loyaltyPointsPerDollar' => '10',
        'loyaltyEnabled' => true,
        'cateringMinimumGuests' => '10',
        'cateringLeadTimeDays' => '14',
        'socialMediaLinks' => [],
        'homepageSections' => [],
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

test('it is bound as a singleton in the container', function () {
    setUpTenantTest();

    $a = app(TenantSettings::class);
    $b = app(TenantSettings::class);

    expect($a)->toBeInstanceOf(TenantSettings::class)
        ->and($a)->toBe($b);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
