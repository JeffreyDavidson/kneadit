<?php

use App\Filament\Pages\Onboarding;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();

    $this->user = User::factory()->owner()->create([

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
    $page = new Onboarding;
    $page->bakery_name = 'Sweet Sunrise Bakery';
    $page->owner_name = 'Jane Baker';

    $reflection = new ReflectionMethod($page, 'saveWelcomeStep');
    $reflection->invoke($page);

    expect(settings('store_name'))->toBe('Sweet Sunrise Bakery');
});

test('contact step saves all contact info', function () {
    $page = new Onboarding;
    $page->contact_email = 'hello@sweetbakery.com';
    $page->contact_phone = '555-123-4567';
    $page->contact_address = '123 Baker St, Tampa, FL 33601';

    $reflection = new ReflectionMethod($page, 'saveContactStep');
    $reflection->invoke($page);

    expect(settings('store_email'))->toBe('hello@sweetbakery.com')->and(settings('store_phone'))->toBe('555-123-4567')->and(settings('store_address'))->toBe('123 Baker St, Tampa, FL 33601');
});

test('branding step saves colors', function () {
    $page = new Onboarding;
    $page->brand_color_primary = '#ff5500';
    $page->brand_color_secondary = '#00aaff';
    $page->store_logo = [];

    $reflection = new ReflectionMethod($page, 'saveBrandingStep');
    $reflection->invoke($page);

    expect(settings('brand_color_primary'))->toBe('#ff5500')->and(settings('brand_color_secondary'))->toBe('#00aaff');
});

test('product step creates product in database', function () {
    $category = Category::factory()->create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $page = new Onboarding;
    $page->product_name = 'Sourdough Loaf';
    $page->product_description = 'A classic sourdough bread';
    $page->product_price = '12.50';
    $page->product_category_id = (string) $category->id;

    $reflection = new ReflectionMethod($page, 'saveProductStep');
    $reflection->invoke($page);

    $this->assertDatabaseHas('products', [
        'name' => 'Sourdough Loaf',
        'slug' => 'sourdough-loaf',
        'price' => 12.50,
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

    $page = new Onboarding;
    $page->product_name = 'Double Chocolate Layer Cake';
    $page->product_description = '';
    $page->product_price = '35.00';
    $page->product_category_id = (string) $category->id;

    $reflection = new ReflectionMethod($page, 'saveProductStep');
    $reflection->invoke($page);

    $this->assertDatabaseHas('products', [
        'slug' => 'double-chocolate-layer-cake',
    ]);
});

test('business hours step saves open days only', function () {
    $page = new Onboarding;
    $page->hours_monday = true;
    $page->hours_monday_open = '08:00';
    $page->hours_monday_close = '17:00';
    $page->hours_tuesday = true;
    $page->hours_tuesday_open = '08:00';
    $page->hours_tuesday_close = '17:00';
    $page->hours_wednesday = false;
    $page->hours_thursday = false;
    $page->hours_friday = true;
    $page->hours_friday_open = '09:00';
    $page->hours_friday_close = '15:00';
    $page->hours_saturday = false;
    $page->hours_sunday = false;

    $reflection = new ReflectionMethod($page, 'saveBusinessHoursStep');
    $reflection->invoke($page);

    $hours = json_decode(settings('operating_hours'), true);

    expect($hours)->toHaveKeys(['monday', 'tuesday'])->not->toHaveKey('wednesday')->not->toHaveKey('thursday')->toHaveKey('friday')->not->toHaveKey('saturday')->not->toHaveKey('sunday')->and($hours['monday'])->toMatchArray(['open' => '08:00', 'close' => '17:00'])->and($hours['friday'])->toMatchArray(['open' => '09:00', 'close' => '15:00']);
});

test('business hours with no days saves empty schedule', function () {
    $page = new Onboarding;
    $page->hours_monday = false;
    $page->hours_tuesday = false;
    $page->hours_wednesday = false;
    $page->hours_thursday = false;
    $page->hours_friday = false;
    $page->hours_saturday = false;
    $page->hours_sunday = false;

    $reflection = new ReflectionMethod($page, 'saveBusinessHoursStep');
    $reflection->invoke($page);

    $hours = json_decode(settings('operating_hours'), true);
    expect($hours)->toBeEmpty();
});

test('compliance step saves state and details', function () {
    $page = new Onboarding;
    $page->cottage_food_state = 'FL';
    $page->revenue_cap = '250000';
    $page->license_number = 'CF-12345';
    $page->allergy_disclaimer = 'We use nuts and dairy.';
    $page->compliance_acknowledged = true;

    $reflection = new ReflectionMethod($page, 'saveComplianceStep');
    $reflection->invoke($page);

    expect(settings('cottage_food_state'))->toBe('FL')->and(settings('revenue_cap'))->toBe('250000')->and(settings('license_number'))->toBe('CF-12345')->and(settings('allergy_disclaimer'))->toBe('We use nuts and dairy.')->and(settings('compliance_acknowledged'))->toBe('1');
});

test('compliance step without license saves empty', function () {
    $page = new Onboarding;
    $page->cottage_food_state = 'TX';
    $page->revenue_cap = '50000';
    $page->license_number = '';
    $page->allergy_disclaimer = 'Please ask about allergens.';
    $page->compliance_acknowledged = true;

    $reflection = new ReflectionMethod($page, 'saveComplianceStep');
    $reflection->invoke($page);

    expect(settings('cottage_food_state'))->toBe('TX')->and(settings('license_number'))->toBeEmpty();
});

test('delivery step saves all delivery settings', function () {
    $page = new Onboarding;
    $page->delivery_enabled = true;
    $page->delivery_radius = '15';
    $page->delivery_fee = '5.00';
    $page->free_delivery_over = true;
    $page->free_delivery_threshold = '50.00';
    $page->delivery_minimum_order = '20.00';
    $page->pickup_enabled = true;
    $page->pickup_instructions = 'Ring the doorbell.';

    $reflection = new ReflectionMethod($page, 'saveDeliveryStep');
    $reflection->invoke($page);

    expect(settings('delivery_enabled'))->toBe('1')->and(settings('delivery_radius'))->toBe('15')->and(settings('delivery_fee'))->toBe('5.00')->and(settings('free_delivery_threshold'))->toBe('50.00')->and(settings('delivery_minimum_order'))->toBe('20.00')->and(settings('pickup_enabled'))->toBe('1')->and(settings('pickup_instructions'))->toBe('Ring the doorbell.');
});

test('delivery disabled still saves pickup settings', function () {
    $page = new Onboarding;
    $page->delivery_enabled = false;
    $page->delivery_radius = '';
    $page->delivery_fee = '';
    $page->free_delivery_over = false;
    $page->free_delivery_threshold = '';
    $page->delivery_minimum_order = '';
    $page->pickup_enabled = true;
    $page->pickup_instructions = 'Come to the back door.';

    $reflection = new ReflectionMethod($page, 'saveDeliveryStep');
    $reflection->invoke($page);

    expect(settings('delivery_enabled'))->toBe('0')->and(settings('pickup_enabled'))->toBe('1')->and(settings('pickup_instructions'))->toBe('Come to the back door.');
});

test('free delivery threshold cleared when disabled', function () {
    $page = new Onboarding;
    $page->delivery_enabled = true;
    $page->delivery_radius = '10';
    $page->delivery_fee = '7.00';
    $page->free_delivery_over = false;
    $page->free_delivery_threshold = '50.00'; // should be ignored
    $page->delivery_minimum_order = '';
    $page->pickup_enabled = false;
    $page->pickup_instructions = '';

    $reflection = new ReflectionMethod($page, 'saveDeliveryStep');
    $reflection->invoke($page);

    expect(settings('free_delivery_threshold'))->toBeNull();
});

test('payment step saves paypal credentials', function () {
    $page = new Onboarding;
    $page->payment_methods = ['paypal', 'cash'];
    $page->paypal_client_id = 'AaBbCcDdEeFf123456';
    $page->paypal_client_secret = 'secret_xyz_789';
    $page->paypal_sandbox = true;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    expect(settings('paypal_client_id'))->toBe('AaBbCcDdEeFf123456')->and(settings('paypal_client_secret'))->toBe('secret_xyz_789')->and(settings('paypal_sandbox'))->toBe('1')->and(settings('payment_methods'))->toBe('["paypal","cash"]');
});

test('payment step with live mode', function () {
    $page = new Onboarding;
    $page->payment_methods = ['paypal'];
    $page->paypal_client_id = 'LiveClientId';
    $page->paypal_client_secret = 'LiveSecret';
    $page->paypal_sandbox = false;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    expect(settings('paypal_sandbox'))->toBe('0');
});

test('payment step with cash only', function () {
    $page = new Onboarding;
    $page->payment_methods = ['cash'];
    $page->paypal_client_id = '';
    $page->paypal_client_secret = '';
    $page->paypal_sandbox = true;

    $reflection = new ReflectionMethod($page, 'savePaymentStep');
    $reflection->invoke($page);

    expect(settings('payment_methods'))->toBe('["cash"]')->and(settings('payment_method'))->toBe('cash');
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
    expect($parsed)->not->toBeNull()->and($parsed->isToday())->toBeTrue();
});

test('onboarding page is hidden from navigation', function () {
    expect(
        (new ReflectionClass(Onboarding::class))
            ->getStaticPropertyValue('shouldRegisterNavigation'),
    )->toBeFalse();
});

test('full onboarding flow saves all settings', function () {
    $page = new Onboarding;

    // Step 1: Welcome
    $page->bakery_name = 'Sunrise Bakery';
    $page->owner_name = 'Jane Baker';
    (new ReflectionMethod($page, 'saveWelcomeStep'))->invoke($page);

    // Step 2: Contact
    $page->contact_email = 'jane@sunrisebakery.com';
    $page->contact_phone = '555-0100';
    $page->contact_address = '100 Main St, Orlando, FL 32801';
    (new ReflectionMethod($page, 'saveContactStep'))->invoke($page);

    // Step 3: Branding
    $page->brand_color_primary = '#8b5e3c';
    $page->brand_color_secondary = '#f0c75e';
    $page->store_logo = [];
    (new ReflectionMethod($page, 'saveBrandingStep'))->invoke($page);

    // Step 4: Product
    $category = Category::factory()->create([
        'name' => 'Cookies',
        'slug' => 'cookies',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $page->product_name = 'Chocolate Chip Cookie';
    $page->product_description = 'Classic and delicious';
    $page->product_price = '3.50';
    $page->product_category_id = (string) $category->id;
    (new ReflectionMethod($page, 'saveProductStep'))->invoke($page);

    // Step 5: Business Hours
    $page->hours_monday = true;
    $page->hours_monday_open = '07:00';
    $page->hours_monday_close = '18:00';
    $page->hours_tuesday = true;
    $page->hours_tuesday_open = '07:00';
    $page->hours_tuesday_close = '18:00';
    $page->hours_wednesday = true;
    $page->hours_wednesday_open = '07:00';
    $page->hours_wednesday_close = '18:00';
    $page->hours_thursday = true;
    $page->hours_thursday_open = '07:00';
    $page->hours_thursday_close = '18:00';
    $page->hours_friday = true;
    $page->hours_friday_open = '07:00';
    $page->hours_friday_close = '18:00';
    $page->hours_saturday = false;
    $page->hours_sunday = false;
    (new ReflectionMethod($page, 'saveBusinessHoursStep'))->invoke($page);

    // Step 6: Compliance
    $page->cottage_food_state = 'FL';
    $page->revenue_cap = '250000';
    $page->license_number = '';
    $page->allergy_disclaimer = 'Contains wheat, eggs, dairy.';
    $page->compliance_acknowledged = true;
    (new ReflectionMethod($page, 'saveComplianceStep'))->invoke($page);

    // Step 7: Delivery
    $page->delivery_enabled = true;
    $page->delivery_radius = '20';
    $page->delivery_fee = '5.00';
    $page->free_delivery_over = true;
    $page->free_delivery_threshold = '50.00';
    $page->delivery_minimum_order = '15.00';
    $page->pickup_enabled = true;
    $page->pickup_instructions = 'Text when you arrive.';
    (new ReflectionMethod($page, 'saveDeliveryStep'))->invoke($page);

    // Step 8: Payment
    $page->payment_methods = ['paypal', 'cash'];
    $page->paypal_client_id = 'test_client_id';
    $page->paypal_client_secret = 'test_secret';
    $page->paypal_sandbox = true;
    (new ReflectionMethod($page, 'savePaymentStep'))->invoke($page);

    // Step 10: Complete
    $page->completeOnboarding();

    // Verify everything was saved
    expect(settings('store_name'))->toBe('Sunrise Bakery');
    expect(settings('store_email'))->toBe('jane@sunrisebakery.com')->and(settings('store_phone'))->toBe('555-0100')->and(settings('brand_color_primary'))->toBe('#8b5e3c')->and(settings('cottage_food_state'))->toBe('FL')->and(settings('delivery_enabled'))->toBe('1')->and(settings('paypal_client_id'))->toBe('test_client_id')->and(settings('onboarding_completed_at'))->not->toBeNull();

    $this->assertDatabaseHas('products', [
        'name' => 'Chocolate Chip Cookie',
        'price' => 3.50,
    ]);

    $hours = json_decode(settings('operating_hours'), true);
    expect($hours)->toHaveCount(5); // Mon-Fri
});
