<?php

use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use App\Filament\Pages\Platform\OnboardingSteps\BusinessHoursStep;
use App\Filament\Pages\Platform\OnboardingSteps\CompleteStep;
use App\Filament\Pages\Platform\OnboardingSteps\ComplianceStep;
use App\Filament\Pages\Platform\OnboardingSteps\ContactStep;
use App\Filament\Pages\Platform\OnboardingSteps\DeliveryStep;
use App\Filament\Pages\Platform\OnboardingSteps\OnboardingStepRegistry;
use App\Filament\Pages\Platform\OnboardingSteps\PreviewStep;
use App\Filament\Pages\Platform\OnboardingSteps\ProductStep;
use App\Filament\Pages\Platform\OnboardingSteps\WelcomeStep;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;

beforeEach(function () {
    setUpTenantTest();

    // Defaults require tenant identity, but their data already lives in the in-memory tenant schema.
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(new Tenant([
        'id' => 'onboarding-test',
        'name' => 'Test Owner',
        'store_name' => 'Test Bakery',
    ]));
});

// --- CompleteStep ---

test('complete step defaults returns empty array', function () {
    $settings = resolve(TenantSettings::class);

    expect(CompleteStep::defaults($settings))->toBeEmpty();
});

// --- PreviewStep ---

test('preview step defaults returns empty array', function () {
    $settings = resolve(TenantSettings::class);

    expect(PreviewStep::defaults($settings))->toBeEmpty();
});

// --- WelcomeStep ---

test('welcome step defaults returns expected keys', function () {
    $defaults = WelcomeStep::defaults(resolve(TenantSettings::class));

    expect($defaults)->toHaveKeys(['bakery_name', 'owner_name']);
});

// --- ContactStep ---

test('contact step defaults returns expected keys', function () {
    $defaults = ContactStep::defaults(resolve(TenantSettings::class));

    expect($defaults)->toHaveKeys(['email', 'phone', 'address']);
});

test('contact step defaults returns stored values', function () {
    settings(['store_email' => 'stored@example.com']);
    settings(['store_phone' => '555-999-8888']);
    settings(['store_address' => '456 Oak Ave']);

    $defaults = ContactStep::defaults(resolve(TenantSettings::class));

    expect($defaults['email'])->toBe('stored@example.com')
        ->and($defaults['phone'])->toBe('555-999-8888')
        ->and($defaults['address'])->toBe('456 Oak Ave');
});

// --- BrandingStep ---

test('branding step defaults returns expected keys', function () {
    $defaults = BrandingStep::defaults(resolve(TenantSettings::class));

    expect($defaults)->toHaveKeys(['color_primary', 'color_secondary', 'store_logo']);
});

// --- ProductStep ---

test('product step defaults returns expected keys', function () {
    $defaults = ProductStep::defaults(resolve(TenantSettings::class));

    expect($defaults)->toHaveKeys(['name', 'description', 'price', 'category_id']);
});

test('product step defaults returns empty strings when no product', function () {
    $defaults = ProductStep::defaults(resolve(TenantSettings::class));

    expect($defaults['name'])->toBeEmpty()
        ->and($defaults['description'])->toBeEmpty()
        ->and($defaults['price'])->toBeEmpty()
        ->and($defaults['category_id'])->toBeEmpty();
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

    $defaults = ProductStep::defaults(resolve(TenantSettings::class));

    expect($defaults['name'])->toBe('Test Sourdough')
        ->and($defaults['description'])->toBe('A test loaf')
        ->and($defaults['price'])->toBe('10.00')
        ->and($defaults['category_id'])->toBe((string) $category->id);
});

// --- BusinessHoursStep ---

test('business hours step defaults returns all day keys', function () {
    $defaults = BusinessHoursStep::defaults(resolve(TenantSettings::class));

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
    $defaults = BusinessHoursStep::defaults(resolve(TenantSettings::class));

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

    $defaults = BusinessHoursStep::defaults(resolve(TenantSettings::class));

    expect($defaults['monday'])->toBeTrue()
        ->and($defaults['monday_open'])->toBe('09:00')
        ->and($defaults['monday_close'])->toBe('16:00')
        ->and($defaults['tuesday'])->toBeFalse()
        ->and($defaults['saturday'])->toBeTrue()
        ->and($defaults['saturday_open'])->toBe('10:00')
        ->and($defaults['saturday_close'])->toBe('14:00');
});

// --- ComplianceStep ---

test('compliance step defaults returns expected keys', function () {
    $defaults = ComplianceStep::defaults(resolve(TenantSettings::class));

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

    $defaults = ComplianceStep::defaults(resolve(TenantSettings::class));

    expect($defaults['cottage_food_state'])->toBe('NY')
        ->and($defaults['revenue_cap'])->toBe('75000')
        ->and($defaults['license_number'])->toBe('LIC-999')
        ->and($defaults['allergy_disclaimer'])->toBe('Contains nuts.')
        ->and($defaults['acknowledged'])->toBeTrue();
});

// --- DeliveryStep ---

test('delivery step defaults returns expected keys', function () {
    $defaults = DeliveryStep::defaults(resolve(TenantSettings::class));

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
    $defaults = DeliveryStep::defaults(resolve(TenantSettings::class));

    expect($defaults['pickup_enabled'])->toBeTrue()
        ->and($defaults['delivery_enabled'])->toBeFalse();
});

// --- OnboardingStepRegistry ---

test('registry defaults returns all step keys', function () {
    $defaults = OnboardingStepRegistry::defaults(resolve(TenantSettings::class));

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
