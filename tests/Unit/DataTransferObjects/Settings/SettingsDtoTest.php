<?php

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\CateringSettings;
use App\DataTransferObjects\Settings\EngagementSettings;
use App\DataTransferObjects\Settings\HomepageSettings;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\DataTransferObjects\Settings\OrderSettings;
use App\DataTransferObjects\Settings\PaymentSettings;
use App\DataTransferObjects\Settings\PolicySettings;
use App\DataTransferObjects\Settings\StoreInfo;
use App\DataTransferObjects\Settings\WebhookSettings;
use Illuminate\Support\Facades\Storage;

// ---------------------------------------------------------------------------
// BrandingSettings
// ---------------------------------------------------------------------------

test('BrandingSettings stores all properties', function () {
    $dto = new BrandingSettings(
        brandColorPrimary: '#ff6b35',
        storefrontTheme: 'modern',
        businessTagline: 'Fresh baked daily',
        aboutUsText: 'We bake with love.',
        heroImage: 'heroes/main.jpg',
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: 'May contain nuts.',
        cateringHeroImage: 'heroes/catering.jpg',
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto)
        ->brandColorPrimary->toBe('#ff6b35')
        ->storefrontTheme->toBe('modern')
        ->businessTagline->toBe('Fresh baked daily')
        ->aboutUsText->toBe('We bake with love.')
        ->heroImage->toBe('heroes/main.jpg')
        ->heroStyle->toBe('overlay')
        ->allergyDisclaimer->toBe('May contain nuts.')
        ->cateringHeroImage->toBe('heroes/catering.jpg')
        ->loyaltyHeroImage->toBeNull()
        ->giftCardsHeroImage->toBeNull();
});

test('BrandingSettings heroImageUrl returns Storage URL when image exists', function () {
    Storage::fake('public');

    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: 'heroes/main.jpg',
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->heroImageUrl())->toContain('heroes/main.jpg');
});

test('BrandingSettings heroImageUrl returns default when no image', function () {
    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->heroImageUrl())->toContain('unsplash.com');
});

test('BrandingSettings cateringHeroImageUrl returns Storage URL when image exists', function () {
    Storage::fake('public');

    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: 'heroes/catering.jpg',
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->cateringHeroImageUrl())->toContain('heroes/catering.jpg');
});

test('BrandingSettings cateringHeroImageUrl returns default when no image', function () {
    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->cateringHeroImageUrl())->toContain('unsplash.com');
});

test('BrandingSettings loyaltyHeroImageUrl returns Storage URL when image exists', function () {
    Storage::fake('public');

    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: 'heroes/loyalty.jpg',
        giftCardsHeroImage: null,
    );

    expect($dto->loyaltyHeroImageUrl())->toContain('heroes/loyalty.jpg');
});

test('BrandingSettings loyaltyHeroImageUrl returns default when no image', function () {
    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->loyaltyHeroImageUrl())->toContain('unsplash.com');
});

test('BrandingSettings giftCardsHeroImageUrl returns Storage URL when image exists', function () {
    Storage::fake('public');

    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: 'heroes/giftcards.jpg',
    );

    expect($dto->giftCardsHeroImageUrl())->toContain('heroes/giftcards.jpg');
});

test('BrandingSettings giftCardsHeroImageUrl returns default when no image', function () {
    $dto = new BrandingSettings(
        brandColorPrimary: '#000',
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'overlay',
        heroTagline: null,
        heroPrimaryCtaText: 'Order Now',
        heroSecondaryCtaText: 'Browse Menu',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
    );

    expect($dto->giftCardsHeroImageUrl())->toContain('unsplash.com');
});

// ---------------------------------------------------------------------------
// CateringSettings
// ---------------------------------------------------------------------------

test('CateringSettings stores all properties', function () {
    $dto = new CateringSettings(
        enabled: true,
        minimumGuests: '10',
        leadTimeDays: '3',
        eventTypes: ['Wedding', 'Birthday Party'],
    );

    expect($dto)
        ->enabled->toBeTrue()
        ->minimumGuests->toBe('10')
        ->leadTimeDays->toBe('3')
        ->eventTypes->toBe(['Wedding', 'Birthday Party']);
});

// ---------------------------------------------------------------------------
// EngagementSettings
// ---------------------------------------------------------------------------

test('EngagementSettings stores all properties', function () {
    $dto = new EngagementSettings(
        birthdayProgramEnabled: true,
        birthdayCouponEnabled: true,
        birthdayDiscountPercentage: 15,
        birthdayCouponValidDays: 7,
        reviewRequestsEnabled: true,
        reviewRequestDelayHours: 24,
        repeatRemindersEnabled: false,
        repeatReminderDays: 30,
        announcementEnabled: true,
        announcementText: 'Grand opening!',
        announcementType: 'info',
        emailOrderPlacedEnabled: true,
        emailOrderConfirmedEnabled: true,
        emailOrderBakingEnabled: false,
        emailOrderReadyEnabled: true,
        emailOrderDeliveredEnabled: true,
        emailOrderCancelledEnabled: true,
        emailOrderMessageEnabled: true,
        emailProductAvailableEnabled: true,
    );

    expect($dto)
        ->birthdayProgramEnabled->toBeTrue()
        ->birthdayCouponEnabled->toBeTrue()
        ->birthdayDiscountPercentage->toBe(15)
        ->birthdayCouponValidDays->toBe(7)
        ->reviewRequestsEnabled->toBeTrue()
        ->reviewRequestDelayHours->toBe(24)
        ->repeatRemindersEnabled->toBeFalse()
        ->repeatReminderDays->toBe(30)
        ->announcementEnabled->toBeTrue()
        ->announcementText->toBe('Grand opening!')
        ->announcementType->toBe('info')
        ->emailOrderPlacedEnabled->toBeTrue()
        ->emailOrderBakingEnabled->toBeFalse();
});

// ---------------------------------------------------------------------------
// HomepageSettings
// ---------------------------------------------------------------------------

test('HomepageSettings stores all properties', function () {
    $dto = new HomepageSettings(
        socialMediaLinks: ['instagram' => 'https://instagram.com/test'],
        operatingHours: ['monday' => ['open' => '9:00', 'close' => '17:00']],
        faqItems: [['question' => 'Q?', 'answer' => 'A.']],
        sections: [
            'hero' => ['visible' => true, 'order' => 1],
            'about' => ['visible' => false, 'order' => 2],
            'faq' => ['visible' => true, 'order' => 3],
        ],
    );

    expect($dto)
        ->socialMediaLinks->toHaveKey('instagram')
        ->operatingHours->toHaveKey('monday')
        ->faqItems->toHaveCount(1)
        ->sections->toHaveCount(3);
});

test('HomepageSettings visibleSections filters and sorts by order', function () {
    $dto = new HomepageSettings(
        socialMediaLinks: [],
        operatingHours: [],
        faqItems: [],
        sections: [
            'about' => ['visible' => true, 'order' => 3],
            'hero' => ['visible' => true, 'order' => 1],
            'hidden' => ['visible' => false, 'order' => 2],
            'faq' => ['visible' => true, 'order' => 2],
        ],
    );

    $visible = $dto->visibleSections();

    expect($visible)->toHaveCount(3)
        ->and($visible->keys()->all())->toBe(['hero', 'faq', 'about']);
});

// ---------------------------------------------------------------------------
// OnboardingSettings
// ---------------------------------------------------------------------------

test('OnboardingSettings stores completedAt', function () {
    $dto = new OnboardingSettings(completedAt: '2026-01-15 10:30:00');
    expect($dto->completedAt)->toBe('2026-01-15 10:30:00');
});

test('OnboardingSettings allows null completedAt', function () {
    $dto = new OnboardingSettings(completedAt: null);
    expect($dto->completedAt)->toBeNull();
});

// ---------------------------------------------------------------------------
// PaymentSettings
// ---------------------------------------------------------------------------

test('PaymentSettings stores methods accepted', function () {
    $dto = new PaymentSettings(methodsAccepted: ['cash', 'card', 'venmo']);

    expect($dto->methodsAccepted)->toBe(['cash', 'card', 'venmo']);
});

// ---------------------------------------------------------------------------
// PolicySettings
// ---------------------------------------------------------------------------

test('PolicySettings stores all properties', function () {
    $dto = new PolicySettings(
        showOnStorefront: true,
        cancellation: 'No cancellations within 24h.',
        deposit: '50% deposit required.',
        refund: 'No refunds.',
        pickup: 'Pickup at store only.',
        additionalTerms: 'See website for details.',
    );

    expect($dto)
        ->showOnStorefront->toBeTrue()
        ->cancellation->toBe('No cancellations within 24h.')
        ->deposit->toBe('50% deposit required.')
        ->refund->toBe('No refunds.')
        ->pickup->toBe('Pickup at store only.')
        ->additionalTerms->toBe('See website for details.');
});

// ---------------------------------------------------------------------------
// WebhookSettings
// ---------------------------------------------------------------------------

test('WebhookSettings can be instantiated', function () {
    $dto = new WebhookSettings;

    expect($dto)->toBeInstanceOf(WebhookSettings::class);
});

// ---------------------------------------------------------------------------
// StoreInfo
// ---------------------------------------------------------------------------

test('StoreInfo stores all properties', function () {
    $dto = new StoreInfo(
        name: 'Sweet Treats',
        email: 'hello@sweet.com',
        phone: '555-1234',
        address: '123 Baker St',
        website: 'https://sweet.com',
        photo: 'photos/store.jpg',
        logo: 'logos/sweet.png',
        tagline: 'Baked fresh daily',
    );

    expect($dto)
        ->name->toBe('Sweet Treats')
        ->email->toBe('hello@sweet.com')
        ->phone->toBe('555-1234')
        ->address->toBe('123 Baker St')
        ->website->toBe('https://sweet.com')
        ->photo->toBe('photos/store.jpg')
        ->logo->toBe('logos/sweet.png')
        ->tagline->toBe('Baked fresh daily');
});

test('StoreInfo logoUrl returns asset path when logo exists', function () {
    $dto = new StoreInfo(
        name: 'Test',
        email: null,
        phone: null,
        address: null,
        website: null,
        photo: null,
        logo: 'logos/test.png',
        tagline: null,
    );

    expect($dto->logoUrl())->toContain('storage/logos/test.png');
});

test('StoreInfo logoUrl returns null when no logo', function () {
    $dto = new StoreInfo(
        name: 'Test',
        email: null,
        phone: null,
        address: null,
        website: null,
        photo: null,
        logo: null,
        tagline: null,
    );

    expect($dto->logoUrl())->toBeNull();
});

// ---------------------------------------------------------------------------
// OrderSettings
// ---------------------------------------------------------------------------

test('OrderSettings stores all properties', function () {
    $dto = new OrderSettings(
        leadTimeHours: 48,
        deliveryEnabled: true,
        freeDeliveryMinimum: '50.00',
        minimumPickupOrderAmount: '10.00',
        minimumDeliveryOrderAmount: '25.00',
        deliveryFeeTiers: [['min' => 0, 'max' => 25, 'fee' => 5.00]],
        defaultDailyCapacity: 20,
    );

    expect($dto)
        ->leadTimeHours->toBe(48)
        ->deliveryEnabled->toBeTrue()
        ->freeDeliveryMinimum->toBe('50.00')
        ->minimumPickupOrderAmount->toBe('10.00')
        ->minimumDeliveryOrderAmount->toBe('25.00')
        ->deliveryFeeTiers->toHaveCount(1)
        ->defaultDailyCapacity->toBe(20);
});

test('OrderSettings leadTimeDays converts hours to days rounding up', function (
    int $hours,
    int $expectedDays,
) {
    $dto = new OrderSettings(
        leadTimeHours: $hours,
        deliveryEnabled: false,
        freeDeliveryMinimum: '0',
        minimumPickupOrderAmount: '0',
        minimumDeliveryOrderAmount: '0',
        deliveryFeeTiers: [],
        defaultDailyCapacity: 20,
    );

    expect($dto->leadTimeDays())->toBe($expectedDays);
})->with([
    '24 hours = 1 day' => [24, 1],
    '25 hours = 2 days' => [25, 2],
    '48 hours = 2 days' => [48, 2],
    '1 hour = 1 day' => [1, 1],
    '72 hours = 3 days' => [72, 3],
]);
