<?php

use App\Filament\Pages\Platform\Onboarding;
use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use App\Filament\Pages\Platform\OnboardingSteps\BusinessHoursStep;
use App\Filament\Pages\Platform\OnboardingSteps\CompleteStep;
use App\Filament\Pages\Platform\OnboardingSteps\ComplianceStep;
use App\Filament\Pages\Platform\OnboardingSteps\ContactStep;
use App\Filament\Pages\Platform\OnboardingSteps\DeliveryStep;
use App\Filament\Pages\Platform\OnboardingSteps\OnboardingStepRegistry;
use App\Filament\Pages\Platform\OnboardingSteps\PaymentsStep;
use App\Filament\Pages\Platform\OnboardingSteps\PreviewStep;
use App\Filament\Pages\Platform\OnboardingSteps\ProductStep;
use App\Filament\Pages\Platform\OnboardingSteps\WelcomeStep;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;

beforeEach(function () {
    setUpTenantTest();
});

// --- CompleteStep ---

test('complete step key is complete', function () {
    expect(CompleteStep::key())->toBe('complete');
});

test('complete step defaults returns empty array', function () {
    $settings = app(TenantSettings::class);

    expect(CompleteStep::defaults($settings))->toBeEmpty();
});

test('complete step save does nothing', function () {
    CompleteStep::save(['some' => 'data']);

    // No exception, no side effects
    expect(true)->toBeTrue();
});

// --- PreviewStep ---

test('preview step key is preview', function () {
    expect(PreviewStep::key())->toBe('preview');
});

test('preview step defaults returns empty array', function () {
    $settings = app(TenantSettings::class);

    expect(PreviewStep::defaults($settings))->toBeEmpty();
});

test('preview step save does nothing', function () {
    PreviewStep::save(['some' => 'data']);

    // No exception, no side effects
    expect(true)->toBeTrue();
});

// --- WelcomeStep ---

test('welcome step key is welcome', function () {
    expect(WelcomeStep::key())->toBe('welcome');
});

test('welcome step defaults returns expected keys', function () {
    $defaults = WelcomeStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys(['bakery_name', 'owner_name']);
});

// --- ContactStep ---

test('contact step key is contact', function () {
    expect(ContactStep::key())->toBe('contact');
});

test('contact step defaults returns expected keys', function () {
    $defaults = ContactStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys(['email', 'phone', 'address']);
});

test('contact step defaults returns stored values', function () {
    settings(['store_email' => 'stored@example.com']);
    settings(['store_phone' => '555-999-8888']);
    settings(['store_address' => '456 Oak Ave']);

    $defaults = ContactStep::defaults(app(TenantSettings::class));

    expect($defaults['email'])->toBe('stored@example.com')
        ->and($defaults['phone'])->toBe('555-999-8888')
        ->and($defaults['address'])->toBe('456 Oak Ave');
});

// --- BrandingStep ---

test('branding step key is branding', function () {
    expect(BrandingStep::key())->toBe('branding');
});

test('branding step defaults returns expected keys', function () {
    $defaults = BrandingStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys(['color_primary', 'color_secondary', 'store_logo']);
});

// --- ProductStep ---

test('product step key is product', function () {
    expect(ProductStep::key())->toBe('product');
});

test('product step defaults returns expected keys', function () {
    $defaults = ProductStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys(['name', 'description', 'price', 'category_id']);
});

test('product step defaults returns empty strings when no product', function () {
    $defaults = ProductStep::defaults(app(TenantSettings::class));

    expect($defaults['name'])->toBe('')
        ->and($defaults['description'])->toBe('')
        ->and($defaults['price'])->toBe('')
        ->and($defaults['category_id'])->toBe('');
});

test('product step defaults loads existing product', function () {
    $category = App\Models\Inventory\Category::factory()->create([
        'name' => 'Bread',
        'slug' => 'bread',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $product = App\Models\Inventory\Product::factory()->create([
        'name' => 'Test Sourdough',
        'description' => 'A test loaf',
        'price' => 10.00,
        'category_id' => $category->id,
    ]);

    settings(['onboarding_product_id' => $product->id]);

    $defaults = ProductStep::defaults(app(TenantSettings::class));

    expect($defaults['name'])->toBe('Test Sourdough')
        ->and($defaults['description'])->toBe('A test loaf')
        ->and($defaults['price'])->toBe('10.00')
        ->and($defaults['category_id'])->toBe((string) $category->id);
});

// --- BusinessHoursStep ---

test('business hours step key is hours', function () {
    expect(BusinessHoursStep::key())->toBe('hours');
});

test('business hours step defaults returns all day keys', function () {
    $defaults = BusinessHoursStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys([
        'monday', 'monday_open', 'monday_close',
        'tuesday', 'tuesday_open', 'tuesday_close',
        'wednesday', 'wednesday_open', 'wednesday_close',
        'thursday', 'thursday_open', 'thursday_close',
        'friday', 'friday_open', 'friday_close',
        'saturday', 'saturday_open', 'saturday_close',
        'sunday', 'sunday_open', 'sunday_close',
    ]);
});

test('business hours step defaults weekdays open weekends closed', function () {
    $defaults = BusinessHoursStep::defaults(app(TenantSettings::class));

    expect($defaults['monday'])->toBeTrue()
        ->and($defaults['tuesday'])->toBeTrue()
        ->and($defaults['wednesday'])->toBeTrue()
        ->and($defaults['thursday'])->toBeTrue()
        ->and($defaults['friday'])->toBeTrue()
        ->and($defaults['saturday'])->toBeFalse()
        ->and($defaults['sunday'])->toBeFalse();
});

test('business hours step defaults loads existing settings', function () {
    $hours = json_encode([
        'monday' => ['open' => '09:00', 'close' => '16:00'],
        'saturday' => ['open' => '10:00', 'close' => '14:00'],
    ]);
    settings(['operating_hours' => $hours]);

    $defaults = BusinessHoursStep::defaults(app(TenantSettings::class));

    expect($defaults['monday'])->toBeTrue()
        ->and($defaults['monday_open'])->toBe('09:00')
        ->and($defaults['monday_close'])->toBe('16:00')
        ->and($defaults['tuesday'])->toBeFalse()
        ->and($defaults['saturday'])->toBeTrue()
        ->and($defaults['saturday_open'])->toBe('10:00')
        ->and($defaults['saturday_close'])->toBe('14:00');
});

// --- ComplianceStep ---

test('compliance step key is compliance', function () {
    expect(ComplianceStep::key())->toBe('compliance');
});

test('compliance step defaults returns expected keys', function () {
    $defaults = ComplianceStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys([
        'cottage_food_state',
        'revenue_cap',
        'license_number',
        'allergy_disclaimer',
        'acknowledged',
    ]);
});

test('compliance step defaults loads existing settings', function () {
    settings(['cottage_food_state' => 'NY']);
    settings(['revenue_cap' => '75000']);
    settings(['license_number' => 'LIC-999']);
    settings(['allergy_disclaimer' => 'Contains nuts.']);
    settings(['compliance_acknowledged' => '1']);

    $defaults = ComplianceStep::defaults(app(TenantSettings::class));

    expect($defaults['cottage_food_state'])->toBe('NY')
        ->and($defaults['revenue_cap'])->toBe('75000')
        ->and($defaults['license_number'])->toBe('LIC-999')
        ->and($defaults['allergy_disclaimer'])->toBe('Contains nuts.')
        ->and($defaults['acknowledged'])->toBeTrue();
});

test('compliance step us states returns all 50 states', function () {
    $states = ComplianceStep::usStates();

    expect($states)->toBeArray()
        ->toHaveCount(50)
        ->toHaveKey('FL', 'Florida')
        ->toHaveKey('CA', 'California')
        ->toHaveKey('NY', 'New York');
});

// --- DeliveryStep ---

test('delivery step key is delivery', function () {
    expect(DeliveryStep::key())->toBe('delivery');
});

test('delivery step defaults returns expected keys', function () {
    $defaults = DeliveryStep::defaults(app(TenantSettings::class));

    expect($defaults)->toHaveKeys([
        'delivery_enabled',
        'delivery_radius',
        'delivery_fee',
        'free_delivery_over',
        'free_delivery_threshold',
        'delivery_minimum_order',
        'pickup_enabled',
        'pickup_instructions',
    ]);
});

test('delivery step defaults pickup enabled by default', function () {
    $defaults = DeliveryStep::defaults(app(TenantSettings::class));

    expect($defaults['pickup_enabled'])->toBeTrue()
        ->and($defaults['delivery_enabled'])->toBeFalse();
});

// --- PaymentsStep ---

test('payments step key is payments', function () {
    expect(PaymentsStep::key())->toBe('payments');
});

// --- OnboardingStepRegistry ---

test('registry defaults returns all step keys', function () {
    $defaults = OnboardingStepRegistry::defaults(app(TenantSettings::class));

    expect($defaults)->toBeArray()
        ->toHaveKeys([
            'welcome',
            'contact',
            'branding',
            'product',
            'hours',
            'compliance',
            'delivery',
            'payments',
            'preview',
            'complete',
        ]);
});

// --- Onboarding page ---

test('onboarding page is accessible to owner', function () {
    $owner = User::factory()->owner()->create();
    $this->actingAs($owner);

    expect(Onboarding::canAccess())->toBeTrue();
});

test('onboarding page is accessible to manager', function () {
    $manager = User::factory()->manager()->create();
    $this->actingAs($manager);

    expect(Onboarding::canAccess())->toBeTrue();
});

test('onboarding page is not accessible to staff', function () {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);

    expect(Onboarding::canAccess())->toBeFalse();
});

test('onboarding page is not accessible to guests', function () {
    expect(Onboarding::canAccess())->toBeFalse();
});
