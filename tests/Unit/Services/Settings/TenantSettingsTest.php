<?php

use App\DataTransferObjects\Settings\OnboardingSettings;
use App\Services\Settings\TenantSettings;

test('it can be constructed with all sub-DTOs', function () {
    $settings = makeTenantSettings(
        store: makeStoreInfo(['name' => 'Test Bakery', 'email' => 'test@bakery.com', 'phone' => '555-1234']),
        orders: makeOrderSettings(['leadTimeHours' => 24, 'deliveryEnabled' => true]),
        loyalty: makeLoyaltySettings(['enabled' => true]),
    );

    expect($settings->store->name)->toBe('Test Bakery')
        ->and($settings->store->email)->toBe('test@bakery.com')
        ->and($settings->orders->leadTimeHours)->toBe(24)
        ->and($settings->orders->deliveryEnabled)->toBeTrue()
        ->and($settings->loyalty->enabled)->toBeTrue();
});

test('heroImageUrl returns storage url when hero image is set', function () {
    Storage::fake('public');
    $settings = makeTenantSettings(branding: makeBrandingSettings(['heroImage' => 'heroes/main.jpg']));

    expect($settings->heroImageUrl())->toContain('heroes/main.jpg');
});

test('heroImageUrl returns default unsplash url when hero image is null', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings(['heroImage' => null]));

    expect($settings->heroImageUrl())->toContain('unsplash.com');
});

test('cateringHeroImageUrl returns catering-specific fallback when null', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings(['cateringHeroImage' => null]));

    expect($settings->cateringHeroImageUrl())->toContain('photo-1555244162');
});

test('loyaltyHeroImageUrl returns default fallback when null', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings(['loyaltyHeroImage' => null]));

    expect($settings->loyaltyHeroImageUrl())->toContain('unsplash.com');
});

test('giftCardsHeroImageUrl returns default fallback when null', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings(['giftCardsHeroImage' => null]));

    expect($settings->giftCardsHeroImageUrl())->toContain('unsplash.com');
});

test('storeLogoUrl returns asset url when logo is set', function () {
    $settings = makeTenantSettings(store: makeStoreInfo(['logo' => 'logos/test.png']));

    expect($settings->storeLogoUrl())->toContain('storage/logos/test.png');
});

test('storeLogoUrl returns null when logo is not set', function () {
    $settings = makeTenantSettings(store: makeStoreInfo(['logo' => null]));

    expect($settings->storeLogoUrl())->toBeNull();
});

test('defaultTagline returns store tagline when set', function () {
    $settings = makeTenantSettings(store: makeStoreInfo(['tagline' => 'Custom tagline']));

    expect($settings->defaultTagline())->toBe('Custom tagline');
});

test('defaultTagline returns generated tagline when not set', function () {
    $settings = makeTenantSettings(store: makeStoreInfo(['name' => 'My Bakery', 'tagline' => null]));

    expect($settings->defaultTagline())->toBe('My Bakery — Fresh baked goods made with love');
});

test('visibleHomepageSections filters and sorts by order', function () {
    $settings = makeTenantSettings(homepage: makeHomepageSettings(['sections' => [
        'hero' => ['visible' => true, 'order' => 3],
        'hidden' => ['visible' => false, 'order' => 1],
        'featured' => ['visible' => true, 'order' => 1],
        'no_visible_key' => ['order' => 2],
    ]]));

    $sections = $settings->visibleHomepageSections();

    expect($sections->keys()->all())->toBe(['featured', 'no_visible_key', 'hero']);
});

test('leadTimeDays calculates days from hours', function () {
    expect(makeTenantSettings(orders: makeOrderSettings(['leadTimeHours' => 24]))->leadTimeDays())->toBe(1)
        ->and(makeTenantSettings(orders: makeOrderSettings(['leadTimeHours' => 48]))->leadTimeDays())->toBe(2)
        ->and(makeTenantSettings(orders: makeOrderSettings(['leadTimeHours' => 36]))->leadTimeDays())->toBe(2);
});

test('brandColorPrimary is accessible via branding sub-DTO', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings(['brandColorPrimary' => '#ff5500']));

    expect($settings->branding->brandColorPrimary)->toBe('#ff5500');
});

test('onboarding completedAt is accessible and nullable', function () {
    $withValue = makeTenantSettings(onboarding: new OnboardingSettings(completedAt: '2026-01-15 10:00:00'));
    $withNull = makeTenantSettings(onboarding: new OnboardingSettings(completedAt: null));

    expect($withValue->onboarding->completedAt)->toBe('2026-01-15 10:00:00')
        ->and($withNull->onboarding->completedAt)->toBeNull();
});

test('cateringEventTypes falls back to enum default labels when not configured', function () {
    setUpTenantTest();

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cateringEventTypes resolves from a stored json array', function () {
    setUpTenantTest();
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(['Kids Party', 'School Function']);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cateringEventTypes falls back to defaults when the stored json is empty', function () {
    setUpTenantTest();
    settings(['catering_event_types' => json_encode([])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('giftCards.presetAmounts defaults to standard amounts', function () {
    $settings = makeTenantSettings();

    expect($settings->giftCards->presetAmounts)->toBe([10, 25, 50, 100]);
});

test('giftCards.presetAmounts can be customized', function () {
    $settings = makeTenantSettings(giftCards: new App\DataTransferObjects\Settings\GiftCardSettings(
        presetAmounts: [5, 15, 30, 75],
        defaultAmount: 25,
    ));

    expect($settings->giftCards->presetAmounts)->toBe([5, 15, 30, 75]);
});

test('giftCards.defaultAmount defaults to 25', function () {
    $settings = makeTenantSettings();

    expect($settings->giftCards->defaultAmount)->toBe(25);
});

test('giftCards.defaultAmount can be customized', function () {
    $settings = makeTenantSettings(giftCards: new App\DataTransferObjects\Settings\GiftCardSettings(
        presetAmounts: [10, 25, 50, 100],
        defaultAmount: 50,
    ));

    expect($settings->giftCards->defaultAmount)->toBe(50);
});

test('hero CTA and tagline properties are accessible via branding sub-DTO', function () {
    $settings = makeTenantSettings(branding: makeBrandingSettings([
        'heroTagline' => 'Freshly baked daily',
        'heroPrimaryCtaText' => 'Place Your Order',
        'heroSecondaryCtaText' => 'See What\'s Fresh',
    ]));

    expect($settings->branding->heroTagline)->toBe('Freshly baked daily')
        ->and($settings->branding->heroPrimaryCtaText)->toBe('Place Your Order')
        ->and($settings->branding->heroSecondaryCtaText)->toBe('See What\'s Fresh');
});

test('hero CTA properties fall back to defaults when unset in settings', function () {
    setUpTenantTest();

    $settings = TenantSettings::resolve();

    expect($settings->branding->heroPrimaryCtaText)->toBe('Order Now')
        ->and($settings->branding->heroSecondaryCtaText)->toBe('Browse Menu')
        ->and($settings->branding->heroTagline)->toBeNull();
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it is bound as a singleton in the container', function () {
    setUpTenantTest();

    $a = app(TenantSettings::class);
    $b = app(TenantSettings::class);

    expect($a)->toBeInstanceOf(TenantSettings::class)
        ->and($a)->toBe($b);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
