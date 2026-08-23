<?php

use App\Filament\Pages\Platform\Onboarding;
use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use App\Filament\Pages\Platform\OnboardingSteps\BusinessHoursStep;
use App\Filament\Pages\Platform\OnboardingSteps\ComplianceStep;
use App\Filament\Pages\Platform\OnboardingSteps\ContactStep;
use App\Filament\Pages\Platform\OnboardingSteps\DeliveryStep;
use App\Filament\Pages\Platform\OnboardingSteps\PaymentsStep;
use App\Filament\Pages\Platform\OnboardingSteps\ProductStep;
use App\Filament\Pages\Platform\OnboardingSteps\WelcomeStep;
use App\Models\Inventory\Category;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    setUpCentralTest();
    tenancy()->initialize(Tenant::factory()->create());

    test()->user = User::factory()->owner()->create([

    ]);
});

test('onboarding page is registered in filament', function () {
    $page = new Onboarding;
    expect($page::$title ?? (new ReflectionClass($page))->getStaticPropertyValue('title'))
        ->toBe('Welcome to KneadIt')->and($page::$shouldRegisterNavigation ?? (new ReflectionClass($page))->getStaticPropertyValue('shouldRegisterNavigation'))->toBeFalse();
});

test('completed onboarding is detected', function () {
    expect(settings('onboarding_completed_at'))->toBeNull();

    settings(['onboarding_completed_at' => now()->toISOString()]);

    expect(settings('onboarding_completed_at'))->not->toBeNull();
});

test('welcome step saves bakery name and owner', function () {
    WelcomeStep::save([
        'bakery_name' => 'Sweet Sunrise Bakery',
        'owner_name' => 'Jane Baker',
    ]);

    expect(settings('store_name'))->toBe('Sweet Sunrise Bakery');
});

test('contact step saves all contact info', function () {
    ContactStep::save([
        'email' => 'hello@sweetbakery.com',
        'phone' => '555-123-4567',
        'address' => '123 Baker St, Tampa, FL 33601',
    ]);

    expect(settings('store_email'))->toBe('hello@sweetbakery.com')
        ->and(settings('store_phone'))->toBe('555-123-4567')
        ->and(settings('store_address'))->toBe('123 Baker St, Tampa, FL 33601');
});

test('branding step saves colors', function () {
    BrandingStep::save([
        'color_primary' => '#ff5500',
        'color_secondary' => '#00aaff',
        'store_logo' => [],
    ]);

    expect(settings('brand_color_primary'))->toBe('#ff5500')
        ->and(settings('brand_color_secondary'))->toBe('#00aaff');
});

test('product step creates product in database', function () {
    $category = Category::factory()->create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    ProductStep::save([
        'name' => 'Sourdough Loaf',
        'description' => 'A classic sourdough bread',
        'price' => '12.50',
        'category_id' => (string) $category->id,
    ]);

    assertDatabaseHas('products', [
        'name' => 'Sourdough Loaf',
        'slug' => 'sourdough-loaf',
        // products.price is bigint cents (migration 2026_04_22_215000).
        'price' => 1250,
        'category_id' => $category->id,
        'is_active' => true,
    ]);
});

test('product step generates slug from name', function () {
    $category = Category::factory()->create([
        'name' => 'Cakes',
        'slug' => 'cakes',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    ProductStep::save([
        'name' => 'Double Chocolate Layer Cake',
        'description' => '',
        'price' => '35.00',
        'category_id' => (string) $category->id,
    ]);

    assertDatabaseHas('products', [
        'slug' => 'double-chocolate-layer-cake',
    ]);
});

test('business hours step saves open days only', function () {
    BusinessHoursStep::save([
        'monday' => true,
        'monday_open' => '08:00',
        'monday_close' => '17:00',
        'tuesday' => true,
        'tuesday_open' => '08:00',
        'tuesday_close' => '17:00',
        'wednesday' => false,
        'wednesday_open' => '07:00',
        'wednesday_close' => '18:00',
        'thursday' => false,
        'thursday_open' => '07:00',
        'thursday_close' => '18:00',
        'friday' => true,
        'friday_open' => '09:00',
        'friday_close' => '15:00',
        'saturday' => false,
        'saturday_open' => '08:00',
        'saturday_close' => '17:00',
        'sunday' => false,
        'sunday_open' => '08:00',
        'sunday_close' => '17:00',
    ]);

    $hours = json_decode(settings('operating_hours'), true);

    expect($hours)->toHaveKeys(['monday', 'tuesday'])
        ->not->toHaveKey('wednesday')
        ->not->toHaveKey('thursday')
        ->toHaveKey('friday')
        ->not->toHaveKey('saturday')
        ->not->toHaveKey('sunday')
        ->and($hours['monday'])->toMatchArray(['open' => '08:00', 'close' => '17:00'])
        ->and($hours['friday'])->toMatchArray(['open' => '09:00', 'close' => '15:00']);
});

test('business hours with no days saves empty schedule', function () {
    BusinessHoursStep::save([
        'monday' => false,
        'monday_open' => '07:00',
        'monday_close' => '18:00',
        'tuesday' => false,
        'tuesday_open' => '07:00',
        'tuesday_close' => '18:00',
        'wednesday' => false,
        'wednesday_open' => '07:00',
        'wednesday_close' => '18:00',
        'thursday' => false,
        'thursday_open' => '07:00',
        'thursday_close' => '18:00',
        'friday' => false,
        'friday_open' => '07:00',
        'friday_close' => '18:00',
        'saturday' => false,
        'saturday_open' => '08:00',
        'saturday_close' => '17:00',
        'sunday' => false,
        'sunday_open' => '08:00',
        'sunday_close' => '17:00',
    ]);

    $hours = json_decode(settings('operating_hours'), true);
    expect($hours)->toBeEmpty();
});

test('compliance step saves state and details', function () {
    ComplianceStep::save([
        'cottage_food_state' => 'FL',
        'revenue_cap' => '250000',
        'license_number' => 'CF-12345',
        'allergy_disclaimer' => 'We use nuts and dairy.',
        'acknowledged' => true,
    ]);

    expect(settings('cottage_food_state'))->toBe('FL')
        ->and(settings('revenue_cap'))->toBe('250000')
        ->and(settings('license_number'))->toBe('CF-12345')
        ->and(settings('allergy_disclaimer'))->toBe('We use nuts and dairy.')
        ->and(settings('compliance_acknowledged'))->toBe('1');
});

test('compliance step without license saves empty', function () {
    ComplianceStep::save([
        'cottage_food_state' => 'TX',
        'revenue_cap' => '50000',
        'license_number' => '',
        'allergy_disclaimer' => 'Please ask about allergens.',
        'acknowledged' => true,
    ]);

    expect(settings('cottage_food_state'))->toBe('TX')
        ->and(settings('license_number'))->toBeEmpty();
});

test('delivery step saves all delivery settings', function () {
    DeliveryStep::save([
        'delivery_enabled' => true,
        'delivery_radius' => '15',
        'delivery_fee' => '5.00',
        'free_delivery_over' => true,
        'free_delivery_threshold' => '50.00',
        'delivery_minimum_order' => '20.00',
        'pickup_enabled' => true,
        'pickup_instructions' => 'Ring the doorbell.',
    ]);

    expect(settings('delivery_enabled'))->toBe('1')
        ->and(settings('delivery_radius'))->toBe('15')
        ->and(settings('delivery_fee'))->toBe('5.00')
        ->and(settings('free_delivery_minimum'))->toBe('50.00')
        ->and(settings('delivery_minimum_order'))->toBe('20.00')
        ->and(settings('pickup_enabled'))->toBe('1')
        ->and(settings('pickup_instructions'))->toBe('Ring the doorbell.');
});

test('delivery disabled still saves pickup settings', function () {
    DeliveryStep::save([
        'delivery_enabled' => false,
        'delivery_radius' => '',
        'delivery_fee' => '',
        'free_delivery_over' => false,
        'free_delivery_threshold' => '',
        'delivery_minimum_order' => '',
        'pickup_enabled' => true,
        'pickup_instructions' => 'Come to the back door.',
    ]);

    expect(settings('delivery_enabled'))->toBe('0')
        ->and(settings('pickup_enabled'))->toBe('1')
        ->and(settings('pickup_instructions'))->toBe('Come to the back door.');
});

test('free delivery threshold cleared when disabled', function () {
    DeliveryStep::save([
        'delivery_enabled' => true,
        'delivery_radius' => '10',
        'delivery_fee' => '7.00',
        'free_delivery_over' => false,
        'free_delivery_threshold' => '50.00', // should be ignored
        'delivery_minimum_order' => '',
        'pickup_enabled' => false,
        'pickup_instructions' => '',
    ]);

    expect(settings('free_delivery_minimum'))->toBeNull();
});

test('payment step saves paypal credentials', function () {
    PaymentsStep::save([
        'payment_methods' => ['paypal', 'cash'],
        'paypal_client_id' => 'AaBbCcDdEeFf123456',
        'paypal_client_secret' => 'secret_xyz_789',
        'paypal_sandbox' => true,
    ]);

    expect(settings('paypal_client_id'))->toBe('AaBbCcDdEeFf123456')
        ->and(settings('paypal_client_secret'))->toBe('secret_xyz_789')
        ->and(settings('paypal_sandbox'))->toBe('1')
        ->and(settings('payment_methods'))->toBe('["paypal","cash"]');
});

test('payment step with live mode', function () {
    PaymentsStep::save([
        'payment_methods' => ['paypal'],
        'paypal_client_id' => 'LiveClientId',
        'paypal_client_secret' => 'LiveSecret',
        'paypal_sandbox' => false,
    ]);

    expect(settings('paypal_sandbox'))->toBe('0');
});

test('payment step with cash only', function () {
    PaymentsStep::save([
        'payment_methods' => ['cash'],
        'paypal_client_id' => '',
        'paypal_client_secret' => '',
        'paypal_sandbox' => true,
    ]);

    expect(settings('payment_methods'))->toBe('["cash"]')
        ->and(settings('payment_method'))->toBe('cash');
    // PayPal credentials should not be saved when paypal not in payment_methods
    expect(settings('paypal_client_id'))->toBeNull();
});

test('complete onboarding sets timestamp', function () {
    expect(settings('onboarding_completed_at'))->toBeNull();

    $page = new Onboarding;
    $page->completeOnboarding();

    expect(settings('onboarding_completed_at'))->not->toBeNull();
});

test('complete onboarding timestamp is valid iso date', function () {
    $page = new Onboarding;
    $page->completeOnboarding();

    $timestamp = settings('onboarding_completed_at');
    $parsed = Date::parse($timestamp);
    expect($parsed)->not->toBeNull()
        ->and($parsed->isToday())->toBeTrue();
});

test('onboarding page is hidden from navigation', function () {
    expect(
        (new ReflectionClass(Onboarding::class))
            ->getStaticPropertyValue('shouldRegisterNavigation'),
    )->toBeFalse();
});

test('full onboarding flow saves all settings', function () {
    // Step 1: Welcome
    WelcomeStep::save([
        'bakery_name' => 'Sunrise Bakery',
        'owner_name' => 'Jane Baker',
    ]);

    // Step 2: Contact
    ContactStep::save([
        'email' => 'jane@sunrisebakery.com',
        'phone' => '555-0100',
        'address' => '100 Main St, Orlando, FL 32801',
    ]);

    // Step 3: Branding
    BrandingStep::save([
        'color_primary' => '#8b5e3c',
        'color_secondary' => '#f0c75e',
        'store_logo' => [],
    ]);

    // Step 4: Product
    $category = Category::factory()->create([
        'name' => 'Cookies',
        'slug' => 'cookies',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    ProductStep::save([
        'name' => 'Chocolate Chip Cookie',
        'description' => 'Classic and delicious',
        'price' => '3.50',
        'category_id' => (string) $category->id,
    ]);

    // Step 5: Business Hours
    BusinessHoursStep::save([
        'monday' => true,
        'monday_open' => '07:00',
        'monday_close' => '18:00',
        'tuesday' => true,
        'tuesday_open' => '07:00',
        'tuesday_close' => '18:00',
        'wednesday' => true,
        'wednesday_open' => '07:00',
        'wednesday_close' => '18:00',
        'thursday' => true,
        'thursday_open' => '07:00',
        'thursday_close' => '18:00',
        'friday' => true,
        'friday_open' => '07:00',
        'friday_close' => '18:00',
        'saturday' => false,
        'saturday_open' => '08:00',
        'saturday_close' => '17:00',
        'sunday' => false,
        'sunday_open' => '08:00',
        'sunday_close' => '17:00',
    ]);

    // Step 6: Compliance
    ComplianceStep::save([
        'cottage_food_state' => 'FL',
        'revenue_cap' => '250000',
        'license_number' => '',
        'allergy_disclaimer' => 'Contains wheat, eggs, dairy.',
        'acknowledged' => true,
    ]);

    // Step 7: Delivery
    DeliveryStep::save([
        'delivery_enabled' => true,
        'delivery_radius' => '20',
        'delivery_fee' => '5.00',
        'free_delivery_over' => true,
        'free_delivery_threshold' => '50.00',
        'delivery_minimum_order' => '15.00',
        'pickup_enabled' => true,
        'pickup_instructions' => 'Text when you arrive.',
    ]);

    // Step 8: Payment
    PaymentsStep::save([
        'payment_methods' => ['paypal', 'cash'],
        'paypal_client_id' => 'test_client_id',
        'paypal_client_secret' => 'test_secret',
        'paypal_sandbox' => true,
    ]);

    // Step 10: Complete
    $page = new Onboarding;
    $page->completeOnboarding();

    // Verify everything was saved
    expect(settings('store_name'))->toBe('Sunrise Bakery');
    expect(settings('store_email'))->toBe('jane@sunrisebakery.com')
        ->and(settings('store_phone'))->toBe('555-0100')
        ->and(settings('brand_color_primary'))->toBe('#8b5e3c')
        ->and(settings('cottage_food_state'))->toBe('FL')
        ->and(settings('delivery_enabled'))->toBe('1')
        ->and(settings('paypal_client_id'))->toBe('test_client_id')
        ->and(settings('onboarding_completed_at'))->not->toBeNull();

    assertDatabaseHas('products', [
        'name' => 'Chocolate Chip Cookie',
        // products.price is bigint cents (migration 2026_04_22_215000).
        'price' => 350,
    ]);

    $hours = json_decode(settings('operating_hours'), true);
    expect($hours)->toHaveCount(5); // Mon-Fri
});
