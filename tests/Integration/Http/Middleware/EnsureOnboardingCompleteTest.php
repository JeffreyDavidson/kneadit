<?php

use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('passes through when no tenant is initialized', function () {
    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for unauthenticated users', function () {
    // Bind a fake tenant so tenant() returns truthy
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing());

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for auth routes', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/auth/login');
    $request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', '/admin/auth/login', []))->name('filament.admin.auth.login'));

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when already on onboarding page', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/onboarding');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire update requests', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire/update');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire hashed paths', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire-abc123');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when onboarding is complete', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $settings = new App\Services\Settings\TenantSettings(
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
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through gracefully when TenantSettings throws exception', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    app()->bind(App\Services\Settings\TenantSettings::class, function () {
        throw new RuntimeException('Settings unavailable');
    });

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects to onboarding when onboardingCompletedAt is null using TenantSettings', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    $this->actingAs($user);

    $settings = new App\Services\Settings\TenantSettings(
        storeName: 'Test',
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
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('onboarding');
});
